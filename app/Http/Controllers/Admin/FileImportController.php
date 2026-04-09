<?php
namespace App\Http\Controllers\Admin;

use App\Models\ParticipantImport;
use App\Imports\ParticipantImportFile;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Jobs\StudentImportJob;
use Inertia\Inertia;

class FileImportController extends Controller
{
	public function index(){
		return Inertia::render('Admin/FileImport/Index');
	}

    public function create(){
		return Inertia::render('Admin/FileImport/Create');
	}

    public function edit($id){
		return view('admin.file_import.edit');
	}

    public function view($id){
		return view('admin.file_import.view');
	}

	public function list(){
		$ParticipantImport = ParticipantImport::get();

		return response()->json(['status' => true, 'data' => $ParticipantImport ]);
	}

	public function save(Request $request,$id = ""){
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

        // sort ตามตัวเลขหน้าชื่อไฟล์
        // usort($files, function ($a, $b) {
        //     preg_match('/^\d+/', $a->getClientOriginalName(), $ma);
        //     preg_match('/^\d+/', $b->getClientOriginalName(), $mb);

        //     return intval($ma[0] ?? 0) <=> intval($mb[0] ?? 0);
        // });

        foreach ($files as $file) {
            
            // เก็บไฟล์ชั่วคราว
            Excel::import(new ParticipantImportFile, $file);
            $fileName = $file->getClientOriginalName();
            $path = public_path('uploads' . $fileName);
            $file->move(public_path('uploads'), $fileName);
            $path = public_path('uploads/' . $fileName);

            // ลบไฟล์หลัง import เสร็จ
            if (file_exists($path)) {
                unlink($path);
            }
        }
		
		return response()->json([
            'success' => true,
            'message' => 'success'
        ]);
		// return back();
	}

    public function update(Request $request, $id = ""){

		$validator = Validator::make($request->all(), [
			'fileUpload' => 'required',
		]);

		if($validator->fails()){
			return response()->json(['status' => false, 'error' => $validator->errors() ]);
		}else{
			if($request->file('fileUpload') != null){
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

	public function resetStudentImport(){
		
		DB::statement("DELETE FROM participant_import");
        DB::statement("ALTER TABLE participant_import AUTO_INCREMENT = 1");

        return back();
	}
}
