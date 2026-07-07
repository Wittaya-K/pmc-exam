<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SeatAssign;
use App\Exports\StudentRecheckExportFile;
use App\Models\TestCenter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StudentRecheckController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(Gate::allows('student_recheck_access'), 403);

        $testCenter = TestCenter::select('test_center')->orderBy('test_center')->groupBy('test_center')->get();
        $fNamelname = SeatAssign::select('first_name_th', 'last_name_th')->orderBy('id', 'asc')->get();
        $room = TestCenter::select('room')->groupBy('room')->get();
        $classLevel = SeatAssign::select('classLevel')->orderBy('classLevel')->groupBy('classLevel')->get();

        return view('admin.student_recheck.index', compact('testCenter', 'fNamelname', 'room','classLevel'));
    }

    public function create()
    {
        abort_unless(Gate::allows('student_recheck_create'), 403);
        return view('admin.student_recheck.create');
    }

    public function store(Request $request)
    {
        abort_unless(Gate::allows('student_recheck_create'), 403);

        SeatAssign::updateOrCreate(
            ['id' => $request->id],
            [
                'first_name_th' => $request->first_name_th,
                'last_name_th' => $request->last_name_th,
                'school' => $request->school,
                'program_name' => $request->program_name,
                'test_center' => $request->test_center,
                'classLevel' => $request->classLevel,
                'room' => $request->room,
                'seat_no' => $request->seat_no,
                'attendance_status' => $request->attendance_status ?? 'pending',
                'absence_reason' => $request->absence_reason,
            ]
        );

        return response()->json(['success' => 'บันทึกข้อมูลสำเร็จ']);
    }

    public function edit($id)
    {
        abort_unless(Gate::allows('student_recheck_edit'), 403);
        $student = SeatAssign::find($id);
        return response()->json($student);
    }

    public function destroy($id)
    {
        abort_unless(Gate::allows('student_recheck_delete'), 403);
        SeatAssign::find($id)->delete();
        return response()->json(['success' => 'ลบข้อมูลสำเร็จ']);
    }

    public function updateAttendance(Request $request)
    {
        abort_unless(Gate::allows('student_recheck_edit'), 403);

        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:seat_assign,id',
            'attendance_status' => 'required|in:pending,present,absent',
            'absence_reason' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $student = SeatAssign::find($request->id);

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
        abort_unless(Gate::allows('student_recheck_edit'), 403);

        $updates = $request->input('updates', []);

        if (empty($updates)) {
            return response()->json(['error' => 'ไม่มีข้อมูลที่ต้องอัพเดท'], 400);
        }

        $successCount = 0;

        foreach ($updates as $update) {
            $student = SeatAssign::find($update['id']);
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

    // แก้ไขใน StudentRecheckController.php

    public function searchStudent(Request $request)
    {
        $testCenter = $request->input('test_center');
        $attendanceStatus = $request->input('attendance_status');
        $roomFilter = $request->input('room_filter'); // เพิ่มบรรทัดนี้

        $query = SeatAssign::select(
            'id',
            'first_name_th',
            'last_name_th',
            'school',
            'program_name',
            'test_center',
            'classLevel',
            'room',
            'seat_no',
            'attendance_status',
            'absence_reason'
        )
            ->where('test_center', '=', $testCenter);

        // กรองตามสถานะถ้ามีการเลือก
        if ($attendanceStatus && $attendanceStatus !== '') {
            $query->where('attendance_status', '=', $attendanceStatus);
        }

        // กรองตามห้องถ้ามีการเลือก (เพิ่มส่วนนี้)
        if ($roomFilter && $roomFilter !== '') {
            $query->where('room', '=', $roomFilter);
        }

        $SeatAssign = $query->get();

        return response()->json(['status' => true, 'data' => $SeatAssign]);
    }

    public function getStudent(Request $request)
    {
        $fNamelname = $request->input('fNamelname');
        $testCenter = $request->input('test_center');
        $attendanceStatus = $request->input('attendance_status');
        $roomFilter = $request->input('room_filter'); // เพิ่มบรรทัดนี้

        // สร้าง query พื้นฐาน
        $query = SeatAssign::select(
            'id',
            'first_name_th',
            'last_name_th',
            'school',
            'program_name',
            'test_center',
            'classLevel',
            'room',
            'seat_no',
            'attendance_status',
            'absence_reason'
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

        // กรองตามสถานะ
        if ($attendanceStatus && $attendanceStatus !== '') {
            $query->where('attendance_status', '=', $attendanceStatus);
        }

        // กรองตามห้อง (เพิ่มส่วนนี้)
        if ($roomFilter && $roomFilter !== '') {
            $query->where('room', '=', $roomFilter);
        }

        $SeatAssign = $query->get();

        return response()->json(['status' => true, 'data' => $SeatAssign]);
    }
    public function exportFile()
    {
        $filename = now()->format('d-m-Y_H-i-s') . '.xlsx';
        return Excel::download(new StudentRecheckExportFile, $filename);
    }
}
