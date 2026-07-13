<?php

namespace App\Support\Pdf;

use TCPDF;

class SeatReportPdf extends TCPDF
{
    public $reportHeader;      // object จาก ReportHeader::first()
    public $centerName;        // ชื่อสนามสอบของ center ปัจจุบัน
    public $buildFloorRoom;    // สถานที่สอบของ center ปัจจุบัน
    public $fontname;          // ชื่อ font ที่ add ไว้แล้ว
    public $isPsuCenter = true; // true = รูปแบบคณะวิทย์ ม.อ., false = รูปแบบศูนย์สอบอื่น

    public function Header()
    {
        if (!$this->fontname || !$this->reportHeader) {
            return;
        }

        $h = $this->isPsuCenter ? 7 : 5;

        $this->SetFont($this->fontname, 'B', 14);
        $this->Cell(0, 7, $this->reportHeader->project_name_th, 0, 1, 'C');
        $this->SetFont($this->fontname, '', 14);
        $this->Cell(0, 6, $this->reportHeader->project_name_en, 0, 1, 'C');
        $this->Cell(0, 6, "วันจันทร์ที่ {$this->reportHeader->exam_date_open}", 0, 1, 'C');
        $this->Cell(0, $this->isPsuCenter ? 6 : 5, "สนามสอบ {$this->centerName}", 0, 1, 'C');

        $this->SetFont($this->fontname, 'B', 14);
        $this->SetTextColor(0, 0, 255);
        $this->Cell(0, $this->isPsuCenter ? 6 : 5, "สถานที่สอบ {$this->buildFloorRoom}", 0, 1, 'C');
        $this->SetTextColor(0, 0, 0);

        if ($this->isPsuCenter) {
            $this->Ln(3);
        }

        // ===== Table header (ซ้ำทุกหน้า) =====
        $this->SetFont($this->fontname, 'B', 14);
        $this->SetFillColor(255, 210, 48);

        if ($this->isPsuCenter) {
            $this->Cell(28, $h, "รหัสประจำตัว", 1, 0, 'C', 1);
            $this->Cell(32, $h, "ชื่อ", 1, 0, 'C', 1);
            $this->Cell(32, $h, "สกุล", 1, 0, 'C', 1);
            $this->Cell(40, $h, "ระดับการสอบ", 1, 0, 'C', 1);
            $this->Cell(18, $h, "แถว", 1, 0, 'C', 1);
            $this->Cell(18, $h, "เลขที่นั่ง", 1, 0, 'C', 1);
            $this->Cell(22, $h, "ลงลายมือชื่อ", 1, 1, 'C', 1);
        } else {
            $this->Cell(28, $h, "รหัสประจำตัว", 1, 0, 'C', 1);
            $this->Cell(32, $h, "ชื่อ", 1, 0, 'C', 1);
            $this->Cell(32, $h, "สกุล", 1, 0, 'C', 1);
            $this->Cell(40, $h, "ระดับการสอบ", 1, 0, 'C', 1);
            $this->Cell(18, $h, "เลขที่นั่งสอบ", 1, 0, 'C', 1);
            $this->Cell(40, $h, "ลงลายมือชื่อ", 1, 1, 'C', 1);
        }

        $this->SetFont($this->fontname, '', 14);
    }
}
