<?php
namespace App\Http\Controllers\Admin;

use App\Exports\ParticipantExportFile;
use App\Http\Controllers\Controller;
use App\Imports\ParticipantImportFile;
use App\Models\ParticipantImport;
use App\Models\SeatAssign;
use App\Models\TestCenter;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class ArrangeSeatController extends Controller
{
    public function index()
    {
        $countStudent    = ParticipantImport::select('id')->count(); //จำนวนนักเรียนทั้งหมด
        $selectRoom      = SeatAssign::select('room')->groupBy('room')->get();
        $countRoom       = count($selectRoom);                     //จำนวนห้องที่จัดสอบแล้ว
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
        $fNamelname = SeatAssign::select('first_name_th', 'last_name_th')->orderBy('id', 'asc')->get();         //ข้อมูลผู้เข้าสอบ

        return view('admin.arrange_seat.view', compact('testCenter', 'fNamelname'));
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
                        $fileName    = $singleFile->getClientOriginalName();
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
                        $fileName    = $singleFile->getClientOriginalName();
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

    public function assignSeats(Request $request)
    {
        // ============================================
        // ส่วนที่ 1: ศูนย์สอบทั่วไป (ไม่มี session A/B)
        // ============================================
        $psuCenter = ParticipantImport::distinct()->where('test_center', 'like', '%คณะวิทยาศาสตร์%ม.อ%')
            ->value('test_center');

        if (!$psuCenter) {
            throw new Exception("ไม่พบข้อมูลศูนย์สอบคณะวิทยาศาสตร์ ม.อ.");
        }

        // หาชื่อจริงจาก TestCenter แยกต่างหาก
        $psuCenterInTestCenter = TestCenter::where('test_center', 'like', '%คณะวิทยาศาสตร์%ม.อ%')
            ->value('test_center');

        if (!$psuCenterInTestCenter) {
            throw new Exception("ไม่พบข้อมูลห้องสอบคณะวิทยาศาสตร์ ม.อ. ใน TestCenter");
        }

        $generalCenters = TestCenter::select('test_center')
            ->where('test_center', '!=', $psuCenterInTestCenter)
            ->groupBy('test_center')
            ->pluck('test_center')
            ->toArray();

        $examRoomsGeneral = TestCenter::select(
            'test_center', 'building', 'floor', 'room', 'capacity', 'session'
        )
            ->whereIn('test_center', $generalCenters)
            ->orderBy('floor')
            ->orderBy('room')
            ->orderBy('test_center')
            ->orderBy('building')
            ->get()
            ->groupBy('test_center');

        $studentsGeneral = ParticipantImport::select(
            'id', 'title_th', 'first_name_th', 'last_name_th',
            'classLevel', 'level', 'school', 'program_name', 'test_center'
        )
            ->where('test_center', '!=', $psuCenter)
            ->orderBy('test_center', 'asc')
            ->orderBy('program_name', 'asc')
            ->orderBy('id', 'asc')
            ->get()
            ->groupBy('test_center');

        $result = [];

        foreach ($studentsGeneral as $center => $studentList) {

            if (! isset($examRoomsGeneral[$center])) {
                throw new Exception("ไม่มีข้อมูลห้องสอบของศูนย์สอบ {$center}");
            }

            $rooms       = $examRoomsGeneral[$center]->values();
            $roomPointer = 0;

            $totalStudents = $studentList->count();
            $capacity      = $rooms[$roomPointer]->capacity;
            $roomsNeeded   = ceil($totalStudents / $capacity);

            if ($roomsNeeded > $rooms->count()) {
                throw new Exception("ห้องสอบไม่เพียงพอ: {$center}");
            }

            $baseSeatsPerRoom = floor($totalStudents / $roomsNeeded);
            $extraSeats       = $totalStudents % $roomsNeeded;
            $studentIndex     = 0;

            for ($i = 0; $i < $roomsNeeded; $i++) {
                if ($studentIndex >= $totalStudents) {
                    break;
                }

                $room          = $rooms[$roomPointer];
                $seatsThisRoom = $baseSeatsPerRoom + ($i < $extraSeats ? 1 : 0);

                for ($seat = 1; $seat <= $seatsThisRoom && $studentIndex < $totalStudents; $seat++) {
                    $student  = $studentList[$studentIndex];
                    $result[] = [
                        'participant_id'   => $student->id,
                        'test_center'      => $center,
                        'program_name'     => $student->program_name,
                        'school'           => $student->school,
                        'classLevel'       => $student->classLevel,
                        'level'            => $student->level,
                        'building'         => $room->building,
                        'build_floor_room' => "อาคาร {$room->building} ชั้น {$room->floor} ห้อง {$room->room}",
                        'floor'         => $room->floor,
                        'room'          => $room->room,
                        'session'       => $room->session,
                        'seat_no'       => $seat,
                        'first_name_th' => $student->first_name_th,
                        'last_name_th'  => $student->last_name_th,
                        'name_th'       => "{$student->title_th}{$student->first_name_th} {$student->last_name_th}",
                    ];
                    $studentIndex++;
                }
                $roomPointer++;
            }
        }

        // บันทึกศูนย์ทั่วไป
        foreach ($result as $row) {
            SeatAssign::updateOrCreate(
                ['id' => $row['participant_id']],
                [
                    'first_name_th'    => $row['first_name_th'],
                    'last_name_th'     => $row['last_name_th'],
                    'school'           => $row['school'],
                    'program_name'     => $row['program_name'],
                    'test_center'      => $row['test_center'],
                    'classLevel'       => $row['classLevel'],
                    'level'            => $row['level'],
                    'build_floor_room' => $row['build_floor_room'],
                    'building'         => $row['building'],
                    'floor'            => $row['floor'],
                    'room'             => $row['room'],
                    'session'          => $row['session'],
                    'seat_no'          => $row['seat_no'],
                ]
            );
        }

        // ============================================
        // ส่วนที่ 2: คณะวิทยาศาสตร์ ม.อ. (มี session A/B)
        // ============================================

        // $psuCenter = 'คณะวิทยาศาสตร์ม.อ.วิทยาเขตหาดใหญ่';

        $examRoomsPsu = TestCenter::select(
            'test_center', 'building', 'floor', 'room', 'capacity', 'session'
        )
            ->where('test_center', '=', $psuCenterInTestCenter)
            ->orderBy('floor')
            ->orderBy('room')
            ->orderBy('session')
            ->get()
            ->groupBy(['test_center', 'session']);

        $studentsPsu = ParticipantImport::select(
            'id', 'title_th', 'first_name_th', 'last_name_th',
            'classLevel', 'level', 'school', 'program_name', 'test_center'
        )
            ->where('test_center', '=', $psuCenter)
            ->orderBy('program_name', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        // ดึง capacity แต่ละ session
        $getCapacity = TestCenter::select('session', DB::raw('SUM(capacity) as total_capacity'))
            ->where('test_center', $psuCenterInTestCenter)
            ->groupBy('session')
            ->pluck('total_capacity', 'session');

        $capacityA = $getCapacity['A'] ?? 0;
        $capacityB = $getCapacity['B'] ?? 0;

        // ดึงชื่อ program ตามลำดับ (เฉพาะ PSU)
        $getProgram = ParticipantImport::select('program_name')
            ->where('test_center', '=', $psuCenter)
            ->groupBy('program_name')
            ->orderBy('program_name', 'asc')
            ->pluck('program_name');

        // ✅ ตรวจสอบว่ามีครบ 3 program
        if ($getProgram->count() < 3) {
            throw new Exception("ข้อมูล program ไม่ครบสำหรับศูนย์สอบ {$psuCenter}");
        }

        $programA = $getProgram[0]; // ประถมปลาย (ป.4 - ป.6)
        $programB = $getProgram[1]; // มัธยมต้น (ม.1 - ม.3)
        $programC = $getProgram[2]; // มัธยมปลาย (ม.4 - ม.6)

        // นับจำนวนนักเรียนแต่ละ program
        $programCounts = [$programA => 0, $programB => 0, $programC => 0];
        foreach ($studentsPsu as $student) {
            if (isset($programCounts[$student->program_name])) {
                $programCounts[$student->program_name]++;
            }
        }

        $p46_total = $programCounts[$programA] ?? 0; // 681
        $m13_total = $programCounts[$programB] ?? 0; // 673
        $m46_total = $programCounts[$programC] ?? 0; // 936

        // ✅ คำนวณการแบ่ง ม.1-3
        // Session A: ป.4-6 (681) + ม.1-3 บางส่วน = 1,178 พอดี
        // Session B: ม.1-3 ที่เหลือ + ม.4-6 (936)
        $m13_to_A = $capacityA - $p46_total; // 1178 - 681 = 497
        $m13_to_B = $m13_total - $m13_to_A;  // 673 - 497 = 176

        // ✅ แบ่งนักเรียนตามแผน
        $programGroups = [
            "{$programA} → Session A" => [],
            "{$programB} → Session A" => [],
            "{$programB} → Session B" => [],
            "{$programC} → Session B" => [],
        ];

        $programSessions = [
            "{$programA} → Session A" => 'A',
            "{$programB} → Session A" => 'A',
            "{$programB} → Session B" => 'B',
            "{$programC} → Session B" => 'B',
        ];

        $p46_count = $m13_count = $m46_count = 0;

        foreach ($studentsPsu as $student) {
            $program = $student->program_name;
            if ($program === $programA) {
                $programGroups["{$programA} → Session A"][] = $student;
                $p46_count++;
            } elseif ($program === $programB) {
                $key = $m13_count < $m13_to_A
                    ? "{$programB} → Session A"
                    : "{$programB} → Session B";
                $programGroups[$key][] = $student;
                $m13_count++;
            } elseif ($program === $programC) {
                $programGroups["{$programC} → Session B"][] = $student;
                $m46_count++;
            }
        }

        // แปลงเป็น Collection
        foreach ($programGroups as $key => $group) {
            $programGroups[$key] = collect($group);
        }

        // ตรวจสอบห้องสอบ PSU
        if (! isset($examRoomsPsu[$psuCenter])) {
            throw new Exception("ไม่มีข้อมูลห้องสอบของศูนย์สอบ {$psuCenter}");
        }

        $sessionsData = $examRoomsPsu[$psuCenter];

        if (! isset($sessionsData['A']) || ! isset($sessionsData['B'])) {
            throw new Exception("ข้อมูล session ไม่ครบสำหรับศูนย์สอบ {$psuCenter}");
        }

        $roomsSessionA = $sessionsData['A']->values();
        $roomsSessionB = $sessionsData['B']->values();

        $roomPointerA = $roomPointerB = 0;
        $lastSeatA    = $lastSeatB    = 0;
        $resultPsu    = [];

        foreach ($programGroups as $groupName => $studentList) {
            if ($studentList->isEmpty()) {
                continue;
            }

            $totalStudents  = $studentList->count();
            $currentSession = $programSessions[$groupName];

            if ($currentSession === 'A') {
                $rooms       = $roomsSessionA;
                $roomPointer = &$roomPointerA;
                $lastSeat    = &$lastSeatA;
            } else {
                $rooms       = $roomsSessionB;
                $roomPointer = &$roomPointerB;
                $lastSeat    = &$lastSeatB;
            }

            $studentIndex = 0;

            while ($studentIndex < $totalStudents) {
                if ($roomPointer >= $rooms->count()) {
                    throw new Exception("ห้องสอบไม่เพียงพอสำหรับ session {$currentSession}: {$psuCenter} ({$groupName})");
                }

                $room     = $rooms[$roomPointer];
                $capacity = $room->capacity;

                $availableSeats    = $capacity - $lastSeat;
                $studentsRemaining = $totalStudents - $studentIndex;
                $seatsToUse        = min($availableSeats, $studentsRemaining);

                for ($i = 0; $i < $seatsToUse; $i++) {
                    $lastSeat++;
                    $student     = $studentList[$studentIndex];
                    $resultPsu[] = [
                        'participant_id'   => $student->id,
                        'test_center'      => $psuCenter,
                        'program_name'     => $student->program_name,
                        'school'           => $student->school,
                        'classLevel'       => $student->classLevel,
                        'level'            => $student->level,
                        'building'         => $room->building,
                        'floor'            => $room->floor,
                        'room'             => $room->room,
                        'session'          => $currentSession,
                        'build_floor_room' => "อาคาร {$room->building} ชั้น {$room->floor} ห้อง {$room->room} แถว {$currentSession}",
                        'seat_no' => $lastSeat,
                        'first_name_th' => $student->first_name_th,
                        'last_name_th' => $student->last_name_th,
                        'name_th' => "{$student->title_th}{$student->first_name_th} {$student->last_name_th}",
                    ];
                    $studentIndex++;
                }

                if ($lastSeat >= $capacity) {
                    $roomPointer++;
                    $lastSeat = 0;
                }
            }
        }

        // บันทึก PSU
        foreach ($resultPsu as $row) {
            SeatAssign::updateOrCreate(
                ['id' => $row['participant_id']],
                [
                    'first_name_th'    => $row['first_name_th'],
                    'last_name_th'     => $row['last_name_th'],
                    'school'           => $row['school'],
                    'program_name'     => $row['program_name'],
                    'test_center'      => $row['test_center'],
                    'classLevel'       => $row['classLevel'],
                    'level'            => $row['level'],
                    'build_floor_room' => $row['build_floor_room'],
                    'building'         => $row['building'],
                    'floor'            => $row['floor'],
                    'room'             => $row['room'],
                    'session'          => $row['session'],
                    'seat_no'          => $row['seat_no'],
                ]
            );
        }

        return response()->json([
            'success'       => true,
            'message'       => 'success',
            'general_count' => count($result),
            'psu_count'     => count($resultPsu),
        ]);
    }

    public function search(Request $request)
    {
        $testCenter = $request->input(key: 'test_center');
        $fNamelname = $request->input(key: 'fNamelname');
        $fname      = null; //ชื่อ
        $lname      = null; //นามสกุล

        if ($fNamelname != '') {
            $exp_fNamelname = explode(',', $fNamelname); //แยกค่าตัวแปรเพื่อเอาชื่อและนามสกุล
            $fname          = $exp_fNamelname[0];
            $lname          = $exp_fNamelname[1];
            $SeatAssign     = SeatAssign::select('id', 'first_name_th', 'last_name_th', 'school', 'program_name', 'test_center', 'classLevel', 'room', 'seat_no')->where(function ($q) use ($fname, $lname) {
                $q->where('first_name_th', '=', $fname)
                    ->Where('last_name_th', '=', $lname);
            })
                ->get();
        }
        if ($fNamelname != '' && $testCenter != '') {
            $exp_fNamelname = explode(',', $fNamelname); //แยกค่าตัวแปรเพื่อเอาชื่อและนามสกุล
            $fname          = $exp_fNamelname[0];
            $lname          = $exp_fNamelname[1];
            $SeatAssign     = SeatAssign::select('id', 'first_name_th', 'last_name_th', 'school', 'program_name', 'test_center', 'classLevel', 'room', 'seat_no')->where(function ($q) use ($fname, $lname, $testCenter) {
                $q->where('first_name_th', '=', $fname)
                    ->Where('last_name_th', '=', $lname)
                    ->Where('test_center', '=', $testCenter);
            })
                ->get();
        } else {
            $SeatAssign = SeatAssign::select('id', 'first_name_th', 'last_name_th', 'school', 'program_name', 'test_center', 'classLevel', 'room', 'seat_no')->where('test_center', '=', $testCenter)
                ->get();
        }

        return response()->json(['status' => true, 'data' => $SeatAssign]);
    }

    public function searchStudent(Request $request)
    {
        $testCenter = $request->input(key: 'test_center');
        $SeatAssign = SeatAssign::select('id', 'first_name_th', 'last_name_th', 'school', 'program_name', 'test_center', 'classLevel', 'room', 'seat_no')->where('test_center', '=', $testCenter)
            ->get();

        return response()->json(['status' => true, 'data' => $SeatAssign]);
    }

    public function getStudent(Request $request)
    {
        $fNamelname = $request->input(key: 'fNamelname');
        $testCenter = $request->input(key: 'test_center');

        // ค้นหาตามศูนย์สอบ
        if ($fNamelname != '' && $testCenter != '') {
                                                         // dd($fNamelname);
            $exp_fNamelname = explode(',', $fNamelname); //แยกค่าตัวแปรเพื่อเอาชื่อและนามสกุล
            $fname          = $exp_fNamelname[0];
            $lname          = $exp_fNamelname[1];
            $SeatAssign     = SeatAssign::select('id', 'first_name_th', 'last_name_th', 'school', 'program_name', 'test_center', 'classLevel', 'room', 'seat_no')->where(function ($q) use ($fname, $lname, $testCenter) {
                $q->where('first_name_th', '=', $fname)
                    ->Where('last_name_th', '=', $lname)
                    ->Where('test_center', '=', $testCenter);
            })
                ->get();

            return response()->json(['status' => true, 'data' => $SeatAssign]);
        } else {                                     // ค้นหาตามชื่อศูนย์สอบ
            $exp_fNamelname = explode(',', $fNamelname); //แยกค่าตัวแปรเพื่อเอาชื่อและนามสกุล
            $fname          = $exp_fNamelname[0];
            $lname          = $exp_fNamelname[1];
            $SeatAssign     = SeatAssign::select('id', 'first_name_th', 'last_name_th', 'school', 'program_name', 'test_center', 'classLevel', 'room', 'seat_no')->where(function ($q) use ($fname, $lname, $testCenter) {
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
