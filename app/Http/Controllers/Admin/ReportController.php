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
use App\Support\Pdf\SeatReportPdf;

class ReportController extends Controller
{
    public function index()
    {
        $testCenter = SeatAssign::select('test_center')->orderBy('test_center')->groupBy('test_center')->get(); //ข้อมูลศูนย์สอบ
        $room = SeatAssign::select('room')->groupBy('room')->get();               //ข้อมูลผู้เข้าสอบ

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


        if ($testCenter == $psuCenterInTestCenter) {

            // รายงานใบเซ็นชื่อคณะวิทยาศาสตร์
            $pdf = new SeatReportPdf();

            $pdf->SetCreator('ระบบจัดสอบ PMC');
            $pdf->SetAuthor('ระบบจัดสอบ PMC');
            $pdf->SetTitle('ใบเซ็นชื่อ');
            $pdf->SetSubject('ใบเซ็นชื่อ');

            $pdf->SetMargins(10, 52, 10);   // เผื่อพื้นที่ header ด้านบนให้พอ (ปรับตามความสูง header จริง)
            $pdf->SetHeaderMargin(10);
            $pdf->SetAutoPageBreak(true, 10);
            $pdf->setPrintHeader(true);     // <-- เปิดใช้ header อัตโนมัติ
            $pdf->setPrintFooter(true);

            $fontPath = public_path('font/THSarabunNew.ttf');
            $fontname = TCPDF_FONTS::addTTFfont($fontPath, 'TrueTypeUnicode', '', 96);
            $pdf->fontname = $fontname;
            $pdf->isPsuCenter = true; // สลับเป็น false ในบล็อกศูนย์สอบอื่นๆ

            $testCenters = SeatAssign::select('test_center', 'room', 'session')
                ->where('test_center', '=', $testCenter)
                ->groupBy('test_center', 'room', 'session')
                ->orderBy('test_center')
                ->orderBy('room')
                ->orderBy('session')
                ->get();

            foreach ($testCenters as $center) {

                $seatAssign = SeatAssign::where('test_center', $center->test_center)
                    ->where('room', $center->room)
                    ->where('session', $center->session)
                    ->orderBy('seat_no')
                    ->get();

                if ($seatAssign->isEmpty()) {
                    continue;
                }

                $buildFloorRoom = $seatAssign->first()->build_floor_room;

                // ตั้งค่าให้ Header() ใช้ได้ก่อนเรียก AddPage
                $pdf->reportHeader = $reportHeader;
                $pdf->centerName = $center->test_center;
                $pdf->buildFloorRoom = $buildFloorRoom;

                $pdf->AddPage('P', 'A4'); // Header() จะถูกเรียกอัตโนมัติที่นี่ และทุกครั้งที่ page break

                $pdf->SetFont($fontname, '', 14);

                foreach ($seatAssign as $row) {
                    $pdf->Cell(28, 7, $row->id, 1, 0, 'C');
                    $pdf->Cell(32, 7, $row->first_name_th, 1);
                    $pdf->Cell(32, 7, $row->last_name_th, 1);
                    $pdf->Cell(40, 7, $row->program_name, 1, 0, 'C');

                    if ($row->session == "A") {
                        $pdf->SetTextColor(255, 0, 0);
                        $pdf->Cell(18, 7, $row->session, 1, 0, 'C');
                    } elseif ($row->session == "B") {
                        $pdf->SetTextColor(0, 0, 255);
                        $pdf->Cell(18, 7, $row->session, 1, 0, 'C');
                    }
                    $pdf->SetTextColor(0, 0, 0);

                    $pdf->Cell(18, 7, $row->seat_no, 1, 0, 'C');
                    $pdf->Cell(22, 7, "", 1, 1);
                }
            }

            if (Storage::disk('public')->exists('reports/ใบเซ็นชื่อผู้เข้าสอบ.pdf')) { //เช็กก่อนว่ามีไฟล์
                Storage::disk('public')->delete('reports/ใบเซ็นชื่อผู้เข้าสอบ.pdf');
            }
            $pdf->Output('ใบเซ็นชื่อผู้เข้าสอบ.pdf', 'I');

            return redirect()->route('admin.reports.pdfFile');
        } else {

            // รายงานใบเซ็นชื่อศูนย์สอบอื่นๆ
            $pdf = new SeatReportPdf();

            $pdf->SetCreator('ระบบจัดสอบ PMC');
            $pdf->SetAuthor('ระบบจัดสอบ PMC');
            $pdf->SetTitle('ใบเซ็นชื่อ');
            $pdf->SetSubject('ใบเซ็นชื่อ');

            $pdf->SetMargins(10, 48, 10);   // เผื่อพื้นที่ header ด้านบนให้พอ (ปรับตามความสูง header จริง)
            $pdf->SetHeaderMargin(10);
            $pdf->SetAutoPageBreak(true, 10);
            $pdf->setPrintHeader(true);     // <-- เปิดใช้ header อัตโนมัติ
            $pdf->setPrintFooter(true);

            $fontPath = public_path('font/THSarabunNew.ttf');
            $fontname = TCPDF_FONTS::addTTFfont($fontPath, 'TrueTypeUnicode', '', 96);
            $pdf->fontname = $fontname;
            $pdf->isPsuCenter = false; // สลับเป็น false ในบล็อกศูนย์สอบอื่นๆ

            $testCenters = SeatAssign::select('test_center', 'room', 'session')
                ->where('test_center', '=', $testCenter)
                ->groupBy('test_center', 'room', 'session')
                ->orderBy('test_center')
                ->orderBy('room')
                ->orderBy('session')
                ->get();

            foreach ($testCenters as $center) {

                $seatAssign = SeatAssign::where('test_center', $center->test_center)
                    ->where('room', $center->room)
                    ->where('session', $center->session)
                    ->orderBy('seat_no')
                    ->get();

                if ($seatAssign->isEmpty()) {
                    continue;
                }

                $buildFloorRoom = $seatAssign->first()->build_floor_room;

                // ตั้งค่าให้ Header() ใช้ได้ก่อนเรียก AddPage
                $pdf->reportHeader = $reportHeader;
                $pdf->centerName = $center->test_center;
                $pdf->buildFloorRoom = $buildFloorRoom;

                $pdf->AddPage('P', 'A4'); // Header() จะถูกเรียกอัตโนมัติที่นี่ และทุกครั้งที่ page break

                $pdf->SetFont($fontname, '', 14);

                foreach ($seatAssign as $row) {
                    $pdf->Cell(28, 7, $row->id, 1, 0, 'C');
                    $pdf->Cell(32, 7, $row->first_name_th, 1);
                    $pdf->Cell(32, 7, $row->last_name_th, 1);
                    $pdf->Cell(40, 7, $row->program_name, 1, 0, 'C');

                    if ($row->session == "A") {
                        $pdf->SetTextColor(255, 0, 0);
                        $pdf->Cell(18, 7, $row->session, 1, 0, 'C');
                    } elseif ($row->session == "B") {
                        $pdf->SetTextColor(0, 0, 255);
                        $pdf->Cell(18, 7, $row->session, 1, 0, 'C');
                    }
                    $pdf->SetTextColor(0, 0, 0);

                    $pdf->Cell(18, 7, $row->seat_no, 1, 0, 'C');
                    $pdf->Cell(40, 7, "", 1, 1);
                }
            }

            if (Storage::disk('public')->exists('reports/ใบเซ็นชื่อผู้เข้าสอบ.pdf')) { //เช็กก่อนว่ามีไฟล์
                Storage::disk('public')->delete('reports/ใบเซ็นชื่อผู้เข้าสอบ.pdf');
            }
            $pdf->Output('ใบเซ็นชื่อผู้เข้าสอบ.pdf', 'I');

            return redirect()->route('admin.reports.pdfFile');
        }
    }
    public function pdfFile()
    {
        return response()->file(storage_path('app/public/reports/ใบเซ็นชื่อผู้เข้าสอบ.pdf'), ['content-type' => 'application/pdf', 'Content-Disposition' => 'inline; filename="ใบเซ็นชื่อผู้เข้าสอบ.pdf"']);
    }
}
