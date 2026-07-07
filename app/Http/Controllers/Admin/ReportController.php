<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SeatAssign;
use Elibyy\TCPDF\Facades\TCPDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use TCPDF_FONTS;
use Illuminate\Support\Facades\Storage;
use App\Models\ParticipantImport;
use App\Models\TestCenter;
use App\Models\ReportHeader;
use Exception;

class ReportController extends Controller
{
	public function index()
	{
		$testCenter = SeatAssign::select('test_center')->orderBy('test_center')->groupBy('test_center')->get(); //ข้อมูลศูนย์สอบ
		$room       = SeatAssign::select('room')->groupBy('room')->get();               //ข้อมูลผู้เข้าสอบ

		return view('admin.reports.index', compact('testCenter', 'room'));
	}

	public function pdfPrint(Request $request)
	{
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

        $reportHeader = ReportHeader::first();  //ตั้งค่าหัวรายงานจากตาราง report_header
		$testCenter = $request->input('test_center'); // ศูนย์สอบ
		// $room = $request->input('room');				// ห้องสอบ

		// รายงานใบเซ็นชื่อคณะวิทยาศาสตร์
		if ($testCenter == $psuCenterInTestCenter) {
			$app = App::getFacadeRoot();
			$pdf = new TCPDF($app);

			$pdf::SetCreator('ระบบจัดสอบ PMC');
			$pdf::SetAuthor('ระบบจัดสอบ PMC');
			$pdf::SetTitle('ใบเซ็นชื่อ');
			$pdf::SetSubject('ใบเซ็นชื่อ');

			$pdf::SetMargins(10, 20, 10);
			$pdf::SetAutoPageBreak(true, 10);
			$pdf::setPrintHeader(false);
			$pdf::setPrintFooter(true);

			$fontPath = public_path('font/THSarabunNew.ttf');
			$fontname = TCPDF_FONTS::addTTFfont(
				$fontPath,
				'TrueTypeUnicode',
				'',
				96
			);

			// ดึงข้อมูลแบ่งตาม session ด้วย
			$testCenters = SeatAssign::select('test_center', 'room', 'session')
				->where('test_center', '=', $testCenter)
				->groupBy('test_center', 'room', 'session') // เพิ่ม session
				->orderBy('test_center')
				->orderBy('room')
				->orderBy('session') // เรียงตาม session
				->get();

			foreach ($testCenters as $center) {

				// ดึงข้อมูลผู้สอบของศูนย์ + ห้องนั้น
				$seatAssign = SeatAssign::where('test_center', $center->test_center)
					->where('room', $center->room)
					->where('session', $center->session) // กรองตาม session
					->orderBy('seat_no')
					->get();

				if ($seatAssign->isEmpty()) {
					continue;
				}

				$pdf::AddPage('P', 'A4');

				$pdf::SetFont($fontname, 'B', 14);

				$buildFloorRoom = $seatAssign->first()->build_floor_room;
				$session        = $center->session; // เก็บค่า session
				// ===== Header =====
				// $pdf::Cell(0, 7, "โครงการสอบแข่งขันคณิตศาสตร์มหาวิทยาลัยสงขลานครินทร์ ประจำปี 2569", 0, 1, 'C');
				// $pdf::SetFont($fontname, '', 14);
				// $pdf::Cell(0, 6, "PSU Mathematics Competition 2026 (PMC 2026)", 0, 1, 'C');
				// $pdf::Cell(0, 6, "วันจันทร์ที่ 19 มกราคม 2569", 0, 1, 'C');
                $pdf::Cell(0, 7, $reportHeader->project_name_th, 0, 1, 'C');
                $pdf::SetFont($fontname, '', 14);
                $pdf::Cell(0, 6, $reportHeader->project_name_en, 0, 1, 'C');
                $pdf::Cell(0, 6, "วันจันทร์ที่ {$reportHeader->exam_date_open}", 0, 1, 'C'); // แสดงสถานที่พร้อม session
				$pdf::Cell(0, 6, "สนามสอบ {$center->test_center}", 0, 1, 'C');

				// แสดงสถานที่พร้อม session
				$pdf::SetFont($fontname, 'B', 14);
				$pdf::SetTextColor(0, 0, 255);
				$pdf::Cell(0, 6, "สถานที่สอบ {$buildFloorRoom}", 0, 1, 'C');
				$pdf::SetTextColor(0, 0, 0);

				$pdf::Ln(3);

				// ===== Table header =====
				$pdf::SetFont($fontname, 'B', 14);
				$pdf::SetFillColor(255, 210, 48);

				$pdf::Cell(28, 7, "รหัสประจำตัว", 1, 0, 'C', 1);
				$pdf::Cell(32, 7, "ชื่อ", 1, 0, 'C', 1);
				$pdf::Cell(32, 7, "สกุล", 1, 0, 'C', 1);
				$pdf::Cell(40, 7, "ระดับการสอบ", 1, 0, 'C', 1);
				$pdf::Cell(18, 7, "แถว", 1, 0, 'C', 1);
				$pdf::Cell(18, 7, "เลขที่นั่ง", 1, 0, 'C', 1);
				$pdf::Cell(22, 7, "ลงลายมือชื่อ", 1, 1, 'C', 1);

				// ===== Data =====
				$pdf::SetFont($fontname, '', 14);

				// ===== Data =====
				foreach ($seatAssign as $row) {
					$pdf::Cell(28, 7, $row->id, 1, 0, 'C');
					$pdf::Cell(32, 7, $row->first_name_th, 1);
					$pdf::Cell(32, 7, $row->last_name_th, 1);
					$pdf::Cell(40, 7, $row->program_name, 1, 0, 'C');
                    $pdf::SetTextColor(200, 41, 9); // Set color to red
					$pdf::Cell(18, 7, $row->session, 1, 0, 'C');
                    $pdf::SetTextColor(0, 0, 0); // Reset color to black
					$pdf::Cell(18, 7, $row->seat_no, 1, 0, 'C');
					$pdf::Cell(22, 7, "", 1, 1);
				}
			}

			// Storage::delete('app/public/reports/ใบเซ็นชื่อผู้เข้าสอบ.pdf');
			if (Storage::disk('public')->exists('reports/ใบเซ็นชื่อผู้เข้าสอบ.pdf')) { //เช็กก่อนว่ามีไฟล์
				Storage::disk('public')->delete('reports/ใบเซ็นชื่อผู้เข้าสอบ.pdf');
			}
			// $pdf::Output('ใบเซ็นชื่อผู้เข้าสอบ.pdf', 'I');
			$pdf::Output(storage_path('app/public/reports/ใบเซ็นชื่อผู้เข้าสอบ.pdf'), 'F');

			return redirect()->route('admin.reports.pdfFile');
		} else {
			// รายงานใบเซ็นชื่อศูนย์สอบอื่นๆ
			$app = App::getFacadeRoot();
			$pdf = new TCPDF($app);

			$pdf::SetCreator('ระบบจัดสอบ PMC');
			$pdf::SetAuthor('ระบบจัดสอบ PMC');
			$pdf::SetTitle('ใบเซ็นชื่อ');
			$pdf::SetSubject('ใบเซ็นชื่อ');

			$pdf::SetMargins(10, 20, 10);
			$pdf::SetAutoPageBreak(true, 10);
			$pdf::setPrintHeader(false);
			$pdf::setPrintFooter(true);

			$fontPath = public_path('font/THSarabunNew.ttf');
			$fontname = TCPDF_FONTS::addTTFfont(
				$fontPath,
				'TrueTypeUnicode',
				'',
				96
			);

			// ดึงข้อมูลแบ่งตาม session ด้วย
			$testCenters = SeatAssign::select('test_center', 'room', 'session')
				->where('test_center', '=', $testCenter)
				->groupBy('test_center', 'room', 'session') // เพิ่ม session
				->orderBy('test_center')
				->orderBy('room')
				->orderBy('session') // เรียงตาม session
				->get();

			foreach ($testCenters as $center) {

				// ดึงข้อมูลผู้สอบของศูนย์ + ห้องนั้น
				$seatAssign = SeatAssign::where('test_center', $center->test_center)
					->where('room', $center->room)
					->where('session', $center->session) // กรองตาม session
					->orderBy('seat_no')
					->get();

				if ($seatAssign->isEmpty()) {
					continue;
				}

				$pdf::AddPage('P', 'A4');

				$pdf::SetFont($fontname, 'B', 14);

				$buildFloorRoom = $seatAssign->first()->build_floor_room;
				$session        = $center->session; // เก็บค่า session

				// ===== Header =====
				// $pdf::Cell(0, 5, "โครงการสอบแข่งขันคณิตศาสตร์มหาวิทยลัยสงขลานครินทร์ ประจำปี 2569", 0, 1, 'C');
				// $pdf::Cell(0, 5, "PSU Mathematics Competition 2026 (PMC 2026)", 0, 1, 'C');
				// $pdf::Cell(0, 5, "วันจันทร์ที่ 19 มกราคม 2569", 0, 1, 'C');
                $pdf::Cell(0, 7, $reportHeader->project_name_th, 0, 1, 'C');
                $pdf::SetFont($fontname, '', 14);
                $pdf::Cell(0, 6, $reportHeader->project_name_en, 0, 1, 'C');
                $pdf::Cell(0, 6, "วันจันทร์ที่ {$reportHeader->exam_date_open}", 0, 1, 'C'); // แสดงสถานที่พร้อม session
				$pdf::Cell(0, 5, "สนามสอบ {$center->test_center}", 0, 1, 'C');
				$pdf::SetTextColor(0, 0, 255); // สีน้ำเงิน
				$pdf::Cell(0, 5, "สถานที่สอบ {$buildFloorRoom}", 0, 1, 'C');
				$pdf::SetTextColor(0, 0, 0);

				// ===== Table header =====
				$pdf::SetFillColor(255, 210, 48);
				$pdf::Cell(28, 5, "รหัสประจำตัว", 1, 0, 'C', 1);
				$pdf::Cell(32, 5, "ชื่อ", 1, 0, 'C', 1);
				$pdf::Cell(32, 5, "สกุล", 1, 0, 'C', 1);
				$pdf::Cell(40, 5, "ระดับการสอบ", 1, 0, 'C', 1);
				$pdf::Cell(18, 5, "เลขที่นั่งสอบ", 1, 0, 'C', 1);
				$pdf::Cell(40, 5, "ลงลายมือชื่อ", 1, 1, 'C', 1);

				// ===== Data =====
				foreach ($seatAssign as $row) {
					$pdf::Cell(28, 5, $row->id, 1, 0, 'C');
					$pdf::Cell(32, 5, $row->first_name_th, 1);
					$pdf::Cell(32, 5, $row->last_name_th, 1);
					$pdf::Cell(40, 5, $row->program_name, 1, 0, 'C');
					$pdf::Cell(18, 5, $row->seat_no, 1, 0, 'C');
					$pdf::Cell(40, 5, "", 1, 1);
				}
			}
			// Storage::delete('app/public/reports/ใบเซ็นชื่อผู้เข้าสอบ.pdf');
			if (Storage::disk('public')->exists('reports/ใบเซ็นชื่อผู้เข้าสอบ.pdf')) { //เช็กก่อนว่ามีไฟล์
				Storage::disk('public')->delete('reports/ใบเซ็นชื่อผู้เข้าสอบ.pdf');
			}

			// $pdf::Output('ใบเซ็นชื่อผู้เข้าสอบ.pdf', 'I');
			$pdf::Output(storage_path('app/public/reports/ใบเซ็นชื่อผู้เข้าสอบ.pdf'), 'F');

			return redirect()->route('admin.reports.pdfFile');
		}
	}
	public function pdfFile()
	{
		return response()->file(storage_path('app/public/reports/ใบเซ็นชื่อผู้เข้าสอบ.pdf'), ['content-type' => 'application/pdf','Content-Disposition' => 'inline; filename="ใบเซ็นชื่อผู้เข้าสอบ.pdf"']);
	}
}
