<?php

namespace App\Http\Controllers\Admin;

use App\Models\ParticipantImport;
use App\Models\SeatAssign;
use App\Imports\ParticipantImportFile;
use App\Exports\ParticipantExportFile;
use App\Models\TestCenter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Exception;
use App\Jobs\ArrangeSeatJob;

class ArrangeSeatController extends Controller
{
	public function index()
	{
		$countStudent = ParticipantImport::select('id')->count(); //จำนวนนักเรียนทั้งหมด
		$selectRoom = SeatAssign::select('room')->groupBy('room')->get();
		$countRoom = count($selectRoom); //จำนวนห้องที่จัดสอบแล้ว
		$countSeatAssign = SeatAssign::select('seat_no')->count(); //จำนวนที่นั่งจัดสอบแล้ว

		$selectTestcenter = SeatAssign::select('test_center')
			->selectRaw('COUNT(*) as total')
			->orderBy('test_center')
			->groupBy('test_center')
			->get(); //จำนวนนักเรียนแต่ละศูนย์สอบ

		return view('admin.arrange_seat.index', compact('countStudent', 'countRoom', 'countSeatAssign', 'selectTestcenter'));
	}

	public function create()
	{
		return view('admin.arrange_seat.create');
	}

	public function edit($id)
	{
		return view('admin.arrange_seat.edit');
	}

	public function view()
	{
		$testCenter = SeatAssign::select('test_center')->orderBy('test_center')->groupBy('test_center')->get(); //ข้อมูลศูนย์สอบ
		$fNamelname = SeatAssign::select('first_name_th', 'last_name_th')->orderBy('id', 'asc')->get(); //ข้อมูลผู้เข้าสอบ

		return view('admin.arrange_seat.view', compact('testCenter', 'fNamelname'));
		// return view('admin.arrange_seat.view', compact('testCenter'));
	}

	public function save(Request $request, $id = "")
	{

		$validator = Validator::make($request->all(), [
			'fileUpload' => 'required',
		]);

		if ($validator->fails()) {
			return response()->json(['status' => false, 'error' => $validator->errors()]);
		} else {
			if ($request->file('fileUpload') != null) {
				$file = $request->file('fileUpload');
				// Check if multiple files are uploaded
				if (is_array($file)) {
					// Loop through each file and get the original name
					$fileNames = [];
					foreach ($file as $singleFile) {
						$fileName = $singleFile->getClientOriginalName();
						$fileNames[] = $singleFile->getClientOriginalName();
						// $singleFile->storeAs('public/uploads', $fileName);
						$singleFile->move(public_path('uploads'), $fileName);
					}
					// Join file names with commas if you want a single string
					$fileName = implode(',', $fileNames);
				} else {
					// Single file upload
					$fileName = $file->getClientOriginalName();
					Excel::import(new ParticipantImportFile, $request->file('fileUpload'));
					return response()->json(['status' => true, 'message' => 'success']);
				}
			} else {
				$fileName = '-';
			}
		}
	}

	public function update(Request $request, $id = "")
	{

		$validator = Validator::make($request->all(), [
			'fileUpload' => 'required',
		]);

		if ($validator->fails()) {
			return response()->json(['status' => false, 'error' => $validator->errors()]);
		} else {
			if ($request->file('fileUpload') != null) {
				$file = $request->file('fileUpload');
				// Check if multiple files are uploaded
				if (is_array($file)) {
					// Loop through each file and get the original name
					$fileNames = [];
					foreach ($file as $singleFile) {
						$fileName = $singleFile->getClientOriginalName();
						$fileNames[] = $singleFile->getClientOriginalName();
						// $singleFile->storeAs('public/uploads', $fileName);
						$singleFile->move(public_path('uploads'), $fileName);
					}
					// Join file names with commas if you want a single string
					$fileName = implode(',', $fileNames);
				} else {
					// Single file upload
					$fileName = $file->getClientOriginalName();
				}
			} else {
				$fileName = '-';
			}
		}
	}

	public function assignSeats(Request $request){

		// ArrangeSeatJob::dispatch();

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

		// return $result;
		return response()->json([
            'success' => true,
            'message' => 'success'
        ]);
	}
	// public function assignSeats(Request $request)
	// {

	// 	ArrangeSeatJob::dispatch();
	// }

	public function search(Request $request)
	{
		$testCenter = $request->input(key: 'test_center');
		$fNamelname = $request->input(key: 'fNamelname');
		$fname = null; //ชื่อ
		$lname = null; //นามสกุล

		if ($fNamelname != '') {
			$exp_fNamelname = explode(',', $fNamelname); //แยกค่าตัวแปรเพื่อเอาชื่อและนามสกุล
			$fname = $exp_fNamelname[0];
			$lname = $exp_fNamelname[1];
			$SeatAssign = SeatAssign::select('id','first_name_th','last_name_th','school','program_name','test_center','classLevel','room','seat_no')->where(function ($q) use ($fname, $lname) {
				$q->where('first_name_th', '=', $fname)
					->Where('last_name_th', '=', $lname);
			})
			->get();
		}
		if ($fNamelname != '' && $testCenter != '') {
			$exp_fNamelname = explode(',', $fNamelname); //แยกค่าตัวแปรเพื่อเอาชื่อและนามสกุล
			$fname = $exp_fNamelname[0];
			$lname = $exp_fNamelname[1];
			$SeatAssign = SeatAssign::select('id','first_name_th','last_name_th','school','program_name','test_center','classLevel','room','seat_no')->where(function ($q) use ($fname, $lname, $testCenter) {
				$q->where('first_name_th', '=', $fname)
					->Where('last_name_th', '=', $lname)
					->Where('test_center', '=', $testCenter);
			})
			->get();
		} else {
			$SeatAssign = SeatAssign::select('id','first_name_th','last_name_th','school','program_name','test_center','classLevel','room','seat_no')->where('test_center', '=', $testCenter)
			->get();
		}

		return response()->json(['status' => true, 'data' => $SeatAssign]);
	}

	public function searchStudent(Request $request){
		$testCenter = $request->input(key: 'test_center');
		$SeatAssign = SeatAssign::select('id','first_name_th','last_name_th','school','program_name','test_center','classLevel','room','seat_no')->where('test_center', '=', $testCenter)
		->get();

		return response()->json(['status' => true, 'data' => $SeatAssign]);
	}

	public function getStudent(Request $request){
		$fNamelname = $request->input(key: 'fNamelname');
		$testCenter = $request->input(key: 'test_center');

		// ค้นหาตามศูนย์สอบ
		if ($fNamelname != '' && $testCenter != '') {
			// dd($fNamelname);
			$exp_fNamelname = explode(',', $fNamelname); //แยกค่าตัวแปรเพื่อเอาชื่อและนามสกุล
			$fname = $exp_fNamelname[0];
			$lname = $exp_fNamelname[1];
			$SeatAssign = SeatAssign::select('id','first_name_th','last_name_th','school','program_name','test_center','classLevel','room','seat_no')->where(function ($q) use ($fname, $lname, $testCenter) {
				$q->where('first_name_th', '=', $fname)
					->Where('last_name_th', '=', $lname)
					->Where('test_center', '=', $testCenter);
			})
			->get();

			return response()->json(['status' => true, 'data' => $SeatAssign]);
		} else { // ค้นหาตามชื่อศูนย์สอบ
			$exp_fNamelname = explode(',', $fNamelname); //แยกค่าตัวแปรเพื่อเอาชื่อและนามสกุล
			$fname = $exp_fNamelname[0];
			$lname = $exp_fNamelname[1];
			$SeatAssign = SeatAssign::select('id','first_name_th','last_name_th','school','program_name','test_center','classLevel','room','seat_no')->where(function ($q) use ($fname, $lname, $testCenter) {
				$q->where('first_name_th', '=', $fname)
					->Where('last_name_th', '=', $lname);
			})
			->get();

			return response()->json(['status' => true, 'data' => $SeatAssign]);
		}

	}

	public function exportFile()
	{
		$filename = now()->format('d-m-Y_H-i-s') . '.xlsx';
		return Excel::download(new ParticipantExportFile, $filename);
	}

	public function resetAssignSeats()
	{

		DB::statement("DELETE FROM seat_assign");
		DB::statement("ALTER TABLE seat_assign AUTO_INCREMENT = 1");

		return back();
	}
}
