<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SeatAssign;
use App\Models\ParticipantImport;
use App\Models\TestCenter;
use App\Exports\StudentRecheckExportFile;
use App\Exports\StudentUpdateExportFile;
use App\Models\StudentTransfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StudentTransferController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(Gate::allows('student_transfer_access'), 403);

        $testCenter = ParticipantImport::select('test_center')->orderBy('test_center')->groupBy('test_center')->get();
        $fNamelname = ParticipantImport::select('first_name_th', 'last_name_th')->orderBy('id', 'asc')->get();
        $classLevel = ParticipantImport::select('classLevel')->orderBy('classLevel')->groupBy('classLevel')->get();
        $building = TestCenter::select('test_center', 'building')->orderBy('test_center')->groupBy('test_center', 'building')->get();
        $floor = TestCenter::select('test_center', 'floor')->orderBy('test_center')->groupBy('test_center', 'floor')->get();
        $room = TestCenter::select('test_center', 'room')->orderBy('test_center')->groupBy('test_center', 'room')->get();
        $programName = ParticipantImport::select('program_name')->orderBy('program_name')->groupBy('program_name')->get();

        return view('admin.student_transfer.index', compact('testCenter', 'fNamelname', 'classLevel', 'building', 'floor', 'room', 'programName'));
    }

    public function create()
    {
        abort_unless(Gate::allows('student_transfer_create'), 403);
        return view('admin.student_transfer.create');
    }

    public function store(Request $request)
    {
        abort_unless(Gate::allows('student_transfer_create'), 403);

        $studentId = $request->input('id');
        $first_name_th = $request->input('first_name_th');
        $last_name_th = $request->input('last_name_th');
        $first_name_en = $request->input('first_name_en');
        $last_name_en = $request->input('last_name_en');
        $school = $request->input('school');
        $program_name = $request->input('program_name');
        $classLevel = $request->input('classLevel');
        $level = $request->input('level');
        $email = $request->input('email');
        $phone = $request->input('phone');
        $test_center_input = $request->input('test_center_input');
        $building = $request->input('building');
        $floor = $request->input('floor');
        $room = $request->input('room');
        $build_floor_room = "อาคาร {$building} ชั้น {$floor} ห้อง {$room}";

        StudentTransfer::updateOrCreate(
            ['id' => $studentId],
            [
                'first_name_th' => $first_name_th,
                'last_name_th' => $last_name_th,
                'first_name_en' => $first_name_en,
                'last_name_en' => $last_name_en,
                'school' => $school,
                'program_name' => $program_name,
                'test_center' => $test_center_input,
                'classLevel' => $classLevel,
                'level' => $level,
                'email' => $email,
                'phone' => $phone,
                'building' => $building,
                'floor' => $floor,
                'room' => $room,
                'build_floor_room' => $build_floor_room
            ]
        );

        return response()->json(['success' => 'บันทึกข้อมูลสำเร็จ']);
    }

    public function edit($id)
    {
        abort_unless(Gate::allows('student_transfer_edit'), 403);
        $student = ParticipantImport::join('seat_assign', 'participant_import.id', '=', 'seat_assign.id')->find($id);
        $studentTransfer = StudentTransfer::where('id', $id) // แก้ชื่อ column ให้ตรงกับ schema จริง
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($transfer) {
                $transfer->created_at_formatted = $transfer->created_at->addYears(543)->format('d/m/Y'); // แปลงวันที่เป็นรูปแบบไทย
                return $transfer;
            });

        return response()->json([
            'student' => $student,
            'studentTransfer' => $studentTransfer
        ]);
    }

    public function destroy($id)
    {
        abort_unless(Gate::allows('student_transfer_delete'), 403);
        ParticipantImport::find($id)->delete();
        return response()->json(['success' => 'ลบข้อมูลสำเร็จ']);
    }

    public function updateAttendance(Request $request)
    {
        abort_unless(Gate::allows('student_transfer_edit'), 403);

        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:seat_assign,id',
            'attendance_status' => 'required|in:pending,present,absent',
            'absence_reason' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $student = ParticipantImport::find($request->id);

        if ($student) {
            $student->attendance_status = $request->attendance_status;
            $student->absence_reason = $request->attendance_status === 'absent' ? $request->absence_reason : null;
            $student->checked_at = now();
            $student->checked_by = Auth::user()->username;
            $student->save();

            return response()->json([
                'success' => true,
                'message' => 'อัพเดทสถานะสำเร็จ',
                'data' => $student
            ]);
        }

        return response()->json(['error' => 'ไม่พบข้อมูล'], 404);
    }

    public function bulkUpdateAttendance(Request $request)
    {
        abort_unless(Gate::allows('student_transfer_edit'), 403);

        $updates = $request->input('updates', []);

        if (empty($updates)) {
            return response()->json(['error' => 'ไม่มีข้อมูลที่ต้องอัพเดท'], 400);
        }

        $successCount = 0;

        foreach ($updates as $update) {
            $student = ParticipantImport::find($update['id']);
            if ($student) {
                $student->attendance_status = $update['attendance_status'];
                $student->absence_reason = $update['attendance_status'] === 'absent' ? $update['absence_reason'] : null;
                $student->checked_at = now();
                $student->checked_by = Auth::user()->username;
                $student->save();
                $successCount++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "อัพเดทสถานะทั้งหมดสำเร็จ ({$successCount} รายการ)",
            'count' => $successCount
        ]);
    }

    public function searchStudent(Request $request)
    {
        $testCenter = $request->input('test_center');
        // $attendanceStatus = $request->input('attendance_status');

        $query = ParticipantImport::select(
            'id',
            'first_name_th',
            'last_name_th',
            'school',
            'program_name',
            'test_center',
            'classLevel',
        )
            ->where('test_center', '=', $testCenter);

        $SeatAssign = $query->get();

        return response()->json(['status' => true, 'data' => $SeatAssign]);
    }

    public function getStudent(Request $request)
    {
        $fNamelname = $request->input('fNamelname');
        $testCenter = $request->input('test_center');
        // $attendanceStatus = $request->input('attendance_status');

        // สร้าง query พื้นฐาน
        $query = ParticipantImport::select(
            'id',
            'first_name_th',
            'last_name_th',
            'school',
            'program_name',
            'test_center',
            'classLevel',
        );

        // ค้นหาตามชื่อ
        if ($fNamelname != '') {
            $exp_fNamelname = explode(',', $fNamelname);
            $fname = $exp_fNamelname[0];
            $lname = $exp_fNamelname[1];

            $query->where('first_name_th', '=', $fname)
                ->where('last_name_th', '=', $lname);
        }

        // ค้นหาตามศูนย์สอบ
        if ($testCenter != '') {
            $query->where('test_center', '=', $testCenter);
        }

        $SeatAssign = $query->get();

        return response()->json(['status' => true, 'data' => $SeatAssign]);
    }
    public function exportFile()
    {
        $filename = now()->format('d-m-Y_H-i-s') . '.xlsx';
        return Excel::download(new StudentUpdateExportFile, $filename);
    }
}
