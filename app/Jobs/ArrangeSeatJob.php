<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ParticipantImportFile;
use Illuminate\Support\Facades\Log;
use App\Models\TestCenter;
use App\Models\ParticipantImport;
use Exception;
use App\Models\SeatAssign;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\ArrangeSeatRun;
use Throwable;

class ArrangeSeatJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 0;
    public $tries = 1;

    private int $runId;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $runId)
    {
        $this->runId = $runId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $run = ArrangeSeatRun::find($this->runId);
        if (!$run) {
            return;
        }

        // Prevent concurrent runs across workers.
        $lock = Cache::lock('arrange_seat:assignSeats', 60 * 60);
        if (!$lock->get()) {
            $run->update([
                'status' => 'failed',
                'finished_at' => now(),
                'error' => 'Another arrange seat job is already running.',
            ]);
            return;
        }

        $run->update([
            'status' => 'running',
            'started_at' => now(),
            'finished_at' => null,
            'error' => null,
        ]);

        try {
		// เงื่อนไขศูนย์สอบทั่วไป
		$examRooms = TestCenter::select(
			'test_center',
			'building',
			'floor',
			'room',
			'capacity',
			'session'
		)
			->where('test_center', '!=', 'คณะวิทยาศาสตร์ม.อ.วิทยาเขตหาดใหญ่')
			->orderBy('floor')
			->orderBy('room')
			->orderBy('test_center')
			->orderBy('building')
			->get()
			->groupBy('test_center'); //ข้อมูลศูนย์สอบ

		$students = ParticipantImport::select(
			'id',
			'title_th',
			'first_name_th',
			'last_name_th',
			'classLevel',
			'level',
			'school',
			'program_name',
			'test_center'
		)
			->where('test_center', '!=', 'คณะวิทยาศาสตร์ม.อ.วิทยาเขตหาดใหญ่')
			->orderBy('test_center', 'asc')   // เรียงตามศูนย์ก่อน
			->orderBy('program_name', 'asc')
			->orderBy('id', 'asc')
			->get()
			->groupBy('test_center'); // แบ่งกลุ่มตามศูนย์

		$result = [];

		foreach ($students as $center => $studentList) {

			if (!isset($examRooms[$center])) {
				throw new Exception("ไม่มีข้อมูลห้องสอบของศูนย์สอบ {$center}");
			}

			$rooms = $examRooms[$center]->values(); // ใช้ห้องของศูนย์นั้นๆ
			$roomPointer = 0; // รีเซ็ตใหม่ทุกศูนย์

			$totalStudents = $studentList->count(); // คนทั้งหมด
			$capacity = $rooms[$roomPointer]->capacity; // ห้องของแต่ละศูนย์สอบ
			$roomsNeeded = ceil($totalStudents / $capacity); // ห้องที่จะต้องใช้จัดที่นั่ง

			// ตรวจว่าห้องพอไหม
			if ($roomsNeeded > $rooms->count()) {
				throw new Exception("ห้องสอบไม่เพียงพอ: {$center}");
			}

			// คำนวณให้คนกระจายเท่าๆ กัน
			$baseSeatsPerRoom = floor($totalStudents / $roomsNeeded); // 462/16 = 28 เฉลี่ยที่นั่งของแต่ละห้องสอบ
			$extraSeats = $totalStudents % $roomsNeeded; // 462 % 16 = 14 // กระจายห้องให้เท่ากัน

			$studentIndex = 0;

			// echo "Test Center: {$center} | Students: {$totalStudents} | Rooms: {$roomsNeeded}\n";

			for ($i = 0; $i < $roomsNeeded; $i++) {
				if ($studentIndex >= $totalStudents) {
					break; // หยุดถ้านักเรียนหมดแล้ว
				}

				$room = $rooms[$roomPointer];

				// ห้องที่ 0-13 ได้ 29 คน (28+1), ห้องที่ 14-15 ได้ 28 คน
				$seatsThisRoom = $baseSeatsPerRoom + ($i < $extraSeats ? 1 : 0);

				// echo "Room {$room->room}: {$seatsThisRoom} seats\n";

				for ($seat = 1; $seat <= $seatsThisRoom && $studentIndex < $totalStudents; $seat++) {
					$student = $studentList[$studentIndex];

					$result[] = [
						'participant_id' => $student->id,
						'test_center'    => $center,
						'program_name'   => $student->program_name, // ยังเก็บ program ไว้
						'school'         => $student->school,
						'classLevel'     => $student->classLevel,
						'level'          => $student->level,
						'building'       => $room->building,
						'build_floor_room' => "อาคาร {$room->building} ชั้น {$room->floor} ห้อง {$room->room}",
						'floor'          => $room->floor,
						'room'           => $room->room,
						'session'		 => $room->session,
						'seat_no'        => $seat,
						'first_name_th'  => $student->first_name_th,
						'last_name_th'   => $student->last_name_th,
						'name_th'        => "{$student->title_th}{$student->first_name_th} {$student->last_name_th}",
					];

					$studentIndex++;
				}

				$roomPointer++;
			}
		}

		foreach ($result as $row) {
			// echo "{$row['test_center']} | {$row['room']} | Seat {$row['seat_no']} | ID {$row['participant_id']} | level {$row['level']} | Name {$row['name_th']} | classLevel {$row['classLevel']} | Program Name {$row['program_name']}\n";

			SeatAssign::updateOrCreate(
				['id' => $row['participant_id']],
				[
					'first_name_th' => $row['first_name_th'],
					'last_name_th' => $row['last_name_th'],
					'school' => $row['school'],
					'program_name' => $row['program_name'],
					'test_center' => $row['test_center'],
					'classLevel' => $row['classLevel'],
					'level' => $row['level'],
					'build_floor_room' => $row['build_floor_room'],
					'building' => $row['building'],
					'floor' => $row['floor'],
					'room' => $row['room'],
					'session' => $row['session'],
					'seat_no' => $row['seat_no']
				]
			);
		}

		$examRooms = TestCenter::select(
			'test_center',
			'building',
			'floor',
			'room',
			'capacity',
			'session'
		)
		->where('test_center', '=', 'คณะวิทยาศาสตร์ม.อ.วิทยาเขตหาดใหญ่')
		->orderBy('floor')
		->orderBy('room')
		->orderBy('session')
		->get()
		->groupBy(['test_center', 'session']);

		$students = ParticipantImport::select(
			'id',
			'title_th',
			'first_name_th',
			'last_name_th',
			'classLevel',
			'level',
			'school',
			'program_name',
			'test_center'
		)
		->where('test_center', '=', 'คณะวิทยาศาสตร์ม.อ.วิทยาเขตหาดใหญ่')
		->orderBy('program_name', 'asc')
		->orderBy('id', 'asc')
		->get();

		$getCapacity = TestCenter::select('session', DB::raw('SUM(capacity) as total_capacity'))
			->where('test_center', 'คณะวิทยาศาสตร์ม.อ.วิทยาเขตหาดใหญ่')
			->groupBy('session')
			->pluck('total_capacity', 'session'); // [session => total_capacity]

		// ============================================
		// คำนวณการแบ่งที่เหมาะสม
		// ============================================

		$center = 'คณะวิทยาศาสตร์ม.อ.วิทยาเขตหาดใหญ่';
		$capacityA = $getCapacity['A'] ?? 0; // session 1
		$capacityB = $getCapacity['B'] ?? 0; // session 2

		$getProgram = ParticipantImport::select('program_name')->groupBy('program_name')->pluck( 'program_name');

		$programA = $getProgram['0'] ?? 0; // ประถมปลาย (ป.4 - ป.6)
		$programB = $getProgram['1'] ?? 0; // มัธยมต้น (ม.1 - ม.3)
		$programC = $getProgram['2'] ?? 0; // มัธยมปลาย (ม.4 - ม.6)
		// echo "  ระดับการสอบ: " . $programA."  ".$programB."  ".$programC.".\n";

		// ============================================
		// คำนวณการแบ่งนักเรียน
		// ============================================

		// $center = 'คณะวิทยาศาสตร์ม.อ.วิทยาเขตหาดใหญ่';
		// $capacityA = 1178;
		// $capacityB = 1197;

		// นับจำนวนนักเรียนแต่ละ program

		$programCounts = [
			$programA => 0,
			$programB => 0,
			$programC => 0,
		];

		// $programCounts = [
		// 	'ประถมปลาย (ป.4 - ป.6)' => 0,
		// 	'มัธยมต้น (ม.1 - ม.3)' => 0,
		// 	'มัธยมปลาย (ม.4 - ม.6)' => 0,
		// ];

		foreach ($students as $student) {
			if (isset($programCounts[$student->program_name])) {
				$programCounts[$student->program_name]++;
			}
		}

		$p46_total = $programCounts['ประถมปลาย (ป.4 - ป.6)']; // 681
		$m13_total = $programCounts['มัธยมต้น (ม.1 - ม.3)'];   // 673
		$m46_total = $programCounts['มัธยมปลาย (ม.4 - ม.6)'];  // 936

		// echo "จำนวนนักเรียนทั้งหมด:\n";
		// echo "  ป.4-6: {$p46_total} คน\n";
		// echo "  ม.1-3: {$m13_total} คน\n";
		// echo "  ม.4-6: {$m46_total} คน\n";
		// echo "  รวม: " . ($p46_total + $m13_total + $m46_total) . " คน\n\n";

		// ============================================
		// คำนวณการแบ่ง ม.1-3
		// ============================================

		// Session A: ป.4-6 (681) + ม.1-3 (บางส่วน) = 1,178
		$m13_to_A = $capacityA - $p46_total; // 1178 - 681 = 497 คน
		$m13_to_B = $m13_total - $m13_to_A;  // 673 - 497 = 176 คน

		// echo "แผนการจัด Session:\n";
		// echo "Session A ({$capacityA} ที่นั่ง):\n";
		// echo "  ├─ ป.4-6: {$p46_total} คน (ทั้งหมด)\n";
		// echo "  └─ ม.1-3: {$m13_to_A} คน\n";
		// echo "  รวม: " . ($p46_total + $m13_to_A) . " คน\n\n";

		// echo "Session B ({$capacityB} ที่นั่ง):\n";
		// echo "  ├─ ม.1-3: {$m13_to_B} คน (ที่เหลือ)\n";
		// echo "  └─ ม.4-6: {$m46_total} คน (ทั้งหมด)\n";
		// echo "  รวม: " . ($m13_to_B + $m46_total) . " คน\n\n";

		// ============================================
		// แบ่งนักเรียนตามแผน
		// ============================================

		$programGroups = [
			'ป.4-6 → Session A' => [],
			'ม.1-3 → Session A' => [],
			'ม.1-3 → Session B' => [],
			'ม.4-6 → Session B' => [],
		];

		$p46_count = 0;
		$m13_count = 0;
		$m46_count = 0;

		foreach ($students as $student) {
			$program = $student->program_name;
			
			if ($program === $programA) {
				$programGroups['ป.4-6 → Session A'][] = $student;
				$p46_count++;
			}
			else if ($program === $programB) {
				if ($m13_count < $m13_to_A) {
					$programGroups['ม.1-3 → Session A'][] = $student;
				} else {
					$programGroups['ม.1-3 → Session B'][] = $student;
				}
				$m13_count++;
			}
			else if ($program === $programC) {
				$programGroups['ม.4-6 → Session B'][] = $student;
				$m46_count++;
			}
		}

		// แปลงเป็น Collection
		foreach ($programGroups as $key => $group) {
			$programGroups[$key] = collect($group);
		}

		// ============================================
		// กำหนด session แต่ละกลุ่ม
		// ============================================

		$programSessions = [
			'ป.4-6 → Session A' => 'A',
			'ม.1-3 → Session A' => 'A',
			'ม.1-3 → Session B' => 'B',
			'ม.4-6 → Session B' => 'B',
		];

		$result = [];

		// ============================================
		// ตรวจสอบข้อมูลห้องสอบ
		// ============================================

		if (!isset($examRooms[$center])) {
			throw new Exception("ไม่มีข้อมูลห้องสอบของศูนย์สอบ {$center}");
		}

		$sessionsData = $examRooms[$center];

		if (!isset($sessionsData['A']) || !isset($sessionsData['B'])) {
			throw new Exception("ข้อมูล session ไม่ครบสำหรับศูนย์สอบ {$center}");
		}

		$roomsSessionA = $sessionsData['A']->values();
		$roomsSessionB = $sessionsData['B']->values();

		$roomPointerA = 0;
		$roomPointerB = 0;
		$lastSeatA = 0;
		$lastSeatB = 0;

		// ============================================
		// จัดที่นั่งแต่ละกลุ่ม
		// ============================================

		foreach ($programGroups as $groupName => $studentList) {
			
			if (collect($studentList)->isEmpty()) continue;
			
			$totalStudents = collect($studentList)->count();
			$currentSession = $programSessions[$groupName];
			
			// เลือกห้องตาม session
			if ($currentSession === 'A') {
				$rooms = $roomsSessionA;
				$roomPointer = &$roomPointerA;
				$lastSeat = &$lastSeatA;
			} else {
				$rooms = $roomsSessionB;
				$roomPointer = &$roomPointerB;
				$lastSeat = &$lastSeatB;
			}

			$studentIndex = 0;

			// echo "{$groupName} | Students: {$totalStudents}\n";

			// จัดที่นั่งทีละห้อง
			while ($studentIndex < $totalStudents) {
				
				if ($roomPointer >= $rooms->count()) {
					throw new Exception("ห้องสอบไม่เพียงพอสำหรับ session {$currentSession}: {$center} ({$groupName})");
				}

				$room = $rooms[$roomPointer];
				$capacity = $room->capacity;

				// echo "  ├─ Room: {$room->room} | Capacity: {$capacity} | Last Seat: {$lastSeat}\n";

				$availableSeats = $capacity - $lastSeat;
				$studentsRemaining = $totalStudents - $studentIndex;
				$seatsToUse = min($availableSeats, $studentsRemaining);

				for ($i = 0; $i < $seatsToUse; $i++) {
					$lastSeat++;
					$student = $studentList[$studentIndex];

					$result[] = [
						'participant_id' => $student->id,
						'test_center'    => $center,
						'program_name'   => $student->program_name, // ชื่อเดิม
						'school'         => $student->school,
						'classLevel'     => $student->classLevel,
						'level'          => $student->level,
						'building'       => $room->building,
						'floor'          => $room->floor,
						'room'           => $room->room,
						'session'        => $currentSession,
						'build_floor_room' => "อาคาร {$room->building} ชั้น {$room->floor} ห้อง {$room->room} แถว {$currentSession}",
						'seat_no'        => $lastSeat,
						'first_name_th'  => $student->first_name_th,
						'last_name_th'   => $student->last_name_th,
						'name_th'        => "{$student->title_th}{$student->first_name_th} {$student->last_name_th}",
					];

					$studentIndex++;
				}

				if ($lastSeat >= $capacity) {
					$roomPointer++;
					$lastSeat = 0;
				}
			}
			
			// echo "\n";
		}

		// ============================================
		// บันทึกข้อมูลลงฐานข้อมูล
		// ============================================

		// echo "บันทึกข้อมูล " . count($result) . " รายการ...\n\n";

		foreach ($result as $row) {
			SeatAssign::updateOrCreate(
				['id' => $row['participant_id']],
				[
					'first_name_th' => $row['first_name_th'],
					'last_name_th' => $row['last_name_th'],
					'school' => $row['school'],
					'program_name' => $row['program_name'],
					'test_center' => $row['test_center'],
					'classLevel' => $row['classLevel'],
					'level' => $row['level'],
					'build_floor_room' => $row['build_floor_room'],
					'building' => $row['building'],
					'floor' => $row['floor'],
					'room' => $row['room'],
					'session' => $row['session'],
					'seat_no' => $row['seat_no']
				]
			);
		}

		// echo "จัดที่นั่งเสร็จสิ้น!\n";

        Log::info('Processing completed for ' . count($result) . ' files');

        $run->update([
            'status' => 'succeeded',
            'finished_at' => now(),
        ]);
        } catch (Throwable $e) {
            $run->update([
                'status' => 'failed',
                'finished_at' => now(),
                'error' => $e->getMessage(),
            ]);

            throw $e;
        } finally {
            optional($lock)->release();
        }
    }
}