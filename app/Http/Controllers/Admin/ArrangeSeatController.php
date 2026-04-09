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
use Inertia\Inertia;
use App\Models\ArrangeSeatRun;
use Illuminate\Support\Facades\Cache;

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

		return Inertia::render('Admin/ArrangeSeat/Index', [
			'countStudent' => $countStudent,
			'countRoom' => $countRoom,
			'countSeatAssign' => $countSeatAssign,
			'selectTestcenter' => $selectTestcenter,
		]);
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

		return Inertia::render('Admin/ArrangeSeat/View', [
			'testCenter' => $testCenter,
			'fNamelname' => $fNamelname,
		]);
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

		// If already assigned, block re-run.
		if (SeatAssign::count() > 0) {
			return response()->json([
				'status' => false,
				'message' => 'มีการจัดที่นั่งสอบแล้ว ไม่สามารถจัดซ้ำได้',
			], 409);
		}

		// Prevent duplicate dispatch (quick lock).
		$dispatchLock = Cache::lock('arrange_seat:assignSeats:dispatch', 30);
		if (!$dispatchLock->get()) {
			return response()->json([
				'status' => false,
				'message' => 'กำลังเริ่มกระบวนการอยู่ กรุณารอสักครู่',
			], 429);
		}

		// If a run is already queued/running, do not create a new one.
		$existing = ArrangeSeatRun::whereIn('status', ['queued', 'running'])->latest('id')->first();
		if ($existing) {
			$dispatchLock->release();
			return response()->json([
				'status' => true,
				'run_id' => $existing->id,
				'message' => 'งานกำลังทำงานอยู่',
			]);
		}

		$run = ArrangeSeatRun::create([
			'status' => 'queued',
			'created_by_user_id' => auth()->id(),
		]);

		ArrangeSeatJob::dispatch($run->id);
		$dispatchLock->release();

		return response()->json([
			'status' => true,
			'run_id' => $run->id,
			'message' => 'queued',
		]);

	}

	public function assignSeatsStatus(Request $request)
	{
		$runId = $request->query('run_id');
		$run = $runId
			? ArrangeSeatRun::find($runId)
			: ArrangeSeatRun::latest('id')->first();

		if (!$run) {
			return response()->json([
				'status' => true,
				'data' => null,
			]);
		}

		return response()->json([
			'status' => true,
			'data' => [
				'id' => $run->id,
				'status' => $run->status,
				'started_at' => optional($run->started_at)->toISOString(),
				'finished_at' => optional($run->finished_at)->toISOString(),
				'error' => $run->error,
			],
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
