<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\CenterImportFile;
use App\Models\TestCenter;
use App\Exports\TestCenterExportFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Jobs\TestCenterJob;

class TestCenterController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(Gate::allows('test_center_access'), 403);

        if ($request->ajax()) {
            $data = TestCenter::all();
            $result = [];
            $index = 1;

            foreach ($data as $row) {
                $result[] = [
                    'id' => $row->id,
                    'test_center'=> $row->test_center,
                    'building'=> $row->building,
                    'floor'=> $row->floor,
                    'room'=> $row->room,
                    'capacity'=> $row->capacity,
                    'air_condition'=> $row->air_condition,
                    'fan'=> $row->fan,
                    'action' => '
                            <a href="javascript:void(0)" data-toggle="tooltip" data-id="' . $row->id . '" data-original-title="Edit" class="edit btn btn-xs btn-warning btn-sm editTestCenter">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="javascript:void(0)" data-toggle="tooltip" data-id="' . $row->id . '" data-original-title="Delete" class="btn btn-xs btn-danger btn-sm deleteTestCenter">
                                <i class="fas fa-trash-alt"></i>
                            </a>'
                ];
            }

            return response()->json(['data' => $result]);
        }

        return view('admin.test_center.index');
    }

    public function create(){
        abort_unless(Gate::allows('test_center_create'), 403);
        
		return view('admin.test_center.create');
	}

	// public function save(Request $request){
    //     $validator = Validator::make($request->all(), [
    //         'fileUpload' => 'required|array',
    //         'fileUpload.*' => 'file|max:102400',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'status' => false,
    //             'error' => $validator->errors()
    //         ]);
    //     }

        

    //     $files = $request->file('fileUpload');
    //     TestCenterJob::dispatch($files); // คำสั่งสร้าง Job สำหรับนำเข้าข้อมูลศูนย์สอบ
        
    //     // sort ตามตัวเลขหน้าชื่อไฟล์
    //     usort($files, function ($a, $b) {
    //         preg_match('/^\d+/', $a->getClientOriginalName(), $ma);
    //         preg_match('/^\d+/', $b->getClientOriginalName(), $mb);

    //         return intval($ma[0] ?? 0) <=> intval($mb[0] ?? 0);
    //     });

    //     foreach ($files as $file) {
            
    //         // เก็บไฟล์ชั่วคราว
    //         Excel::import(new CenterImportFile, $file);
    //         $fileName = $file->getClientOriginalName();
    //         $path = public_path('uploads' . $fileName);
    //         $file->move(public_path('uploads'), $fileName);
    //         $path = public_path('uploads/' . $fileName);

    //         // ลบไฟล์หลัง import เสร็จ
    //         if (file_exists($path)) {
    //             unlink($path);
    //         }
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'success'
    //     ]);
		
	// 	return back();
	// }

    public function save(Request $request){
        $validator = Validator::make($request->all(), [
            'fileUpload' => 'required|array',
            'fileUpload.*' => 'file|max:102400',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'error' => $validator->errors()
            ]);
        }

        $files = $request->file('fileUpload');
        $filePaths = [];
        
        // เก็บไฟล์ชั่วคราวก่อน
        foreach ($files as $file) {
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/temp'), $fileName);
            $filePaths[] = [
                'path' => public_path('uploads/temp/' . $fileName),
                'original_name' => $file->getClientOriginalName()
            ];
        }
        
        // ส่ง path ไปให้ Job แทน
        TestCenterJob::dispatch($filePaths);
        
        return response()->json([
            'success' => true,
            'message' => 'Files are being processed'
        ]);
    }

    public function store(Request $request)
    {
        abort_unless(Gate::allows('test_center_create'), 403);
        TestCenter::updateOrCreate(
            [
                'id' => $request->id
            ],
            [
                'test_center'=> $request->test_center,
                'building'=> $request->building,
                'floor'=> $request->floor,
                'room'=> $request->room,
                'capacity'=> $request->capacity,
                'air_condition'=> $request->air_condition,
                'fan'=> $request->fan,
            ]
        );

        return response()->json(['success' => 'saved successfully.']);
    }

    public function edit($id)
    {
        abort_unless(Gate::allows('test_center_edit'), 403);
        $testCenter = TestCenter::find($id);
        return response()->json($testCenter);
    }

    public function destroy($id)
    {
        abort_unless(Gate::allows('test_center_delete'), 403);
        
        $testCenter = TestCenter::find($id);
        if ($testCenter) {
            $testCenter->forceDelete(); // ลบถาวร
        }

        return response()->json(['success' => 'deleted successfully.']);
    }

    // ฟังก์ชันลบหลายรายการพร้อมกัน
    public function bulkDelete(Request $request)
    {
        abort_unless(Gate::allows('test_center_delete'), 403);

        $ids = $request->ids;
        
        if (empty($ids)) {
            return response()->json([
                'success' => false,
                'message' => 'No items selected'
            ], 400);
        }

        try {
            // ใช้ forceDelete() เพื่อลบถาวร (ไม่ใช่ SoftDelete)
            $testCenters = TestCenter::whereIn('id', $ids)->get();
            foreach ($testCenters as $testCenter) {
                $testCenter->forceDelete();
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Deleted ' . count($ids) . ' items successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting items: ' . $e->getMessage()
            ], 500);
        }
    }

    public function resetTestCenter(){
		
		DB::statement("DELETE FROM test_center");
        DB::statement("ALTER TABLE test_center AUTO_INCREMENT = 1");

        return back();
	}

    public function exportFile()
	{
		$filename = "ศูนย์สอบ - ".now()->format('d-m-Y_H-i-s') . '.xlsx';
		return Excel::download(new TestCenterExportFile, $filename);
	}
}