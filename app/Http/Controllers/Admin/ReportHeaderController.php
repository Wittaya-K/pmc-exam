<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReportHeader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ReportHeaderController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(Gate::allows('report_header_access'), 403);

        if ($request->ajax()) {
            $data = ReportHeader::all();
            $result = [];
            $index = 1;

            foreach ($data as $row) {
                $result[] = [
                    'id' => $row->id,
                    'project_name_th' => $row->project_name_th,
                    'project_name_en' => $row->project_name_en,
                    'exam_date_open' => $row->exam_date_open,
                    'action' => '
                            <a href="javascript:void(0)" data-toggle="tooltip" data-id="' . $row->id . '" data-original-title="Edit" class="btn btn-sm btn-warning editReportHeader">
                                <i class="fas fa-edit"></i>
                            </a>'
                    // 'action' => '
                    //         <a href="javascript:void(0)" data-toggle="tooltip" data-id="' . $row->id . '" data-original-title="Edit" class="btn btn-sm btn-info editReportHeader">
                    //             <i class="fas fa-edit"></i>
                    //         </a>
                    //         <a href="javascript:void(0)" data-toggle="tooltip" data-id="' . $row->id . '" data-original-title="Delete" class="btn btn-sm btn-danger deleteReportHeader">
                    //             <i class="fas fa-trash-alt"></i>
                    //         </a>'
                ];
            }

            return response()->json(['data' => $result]);
        }

        return view('admin.report_header.index');
    }

    public function create(){
        abort_unless(Gate::allows('report_header_create'), 403);

		return view('admin.report_header.create');
	}

    public function store(Request $request)
    {
        abort_unless(Gate::allows('report_header_create'), 403);

        ReportHeader::updateOrCreate(
            [
                'id' => $request->id
            ],
            [
                'project_name_th' => $request->project_name_th,
                'project_name_en' => $request->project_name_en,
                'exam_date_open' => $request->exam_date_open,
            ]
        );

        return response()->json(['success' => 'saved successfully.']);
    }

    public function edit($id)
    {
        abort_unless(Gate::allows('report_header_edit'), 403);
        $reportHeader = ReportHeader::find($id);
        return response()->json($reportHeader);
    }

    public function destroy($id)
    {
        abort_unless(Gate::allows('report_header_delete'), 403);

        $reportHeader = ReportHeader::find($id);
        if ($reportHeader) {
            $reportHeader->forceDelete(); // ลบถาวร
        }

        return response()->json(['success' => 'deleted successfully.']);
    }
}
