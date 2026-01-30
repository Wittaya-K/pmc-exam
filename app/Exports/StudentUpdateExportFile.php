<?php

namespace App\Exports;

use App\Models\ParticipantImport;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
class StudentUpdateExportFile implements FromQuery, WithHeadings, WithChunkReading,WithColumnWidths,WithStyles
{

    public function query()
    {
        return ParticipantImport::select(
            'id',
            'title_th',
            'first_name_th',
            'last_name_th',
            'title_en',
            'first_name_en',
            'last_name_en',
            'email',
            'phone',
            'classLevel',
            'level',
            'program_name',
            'test_center',
            'school',
            'school_sub_district',
            'school_district',
            'school_province',
            'payment_status',
            'payment_status_code',
        )
        ->orderBy('test_center', 'asc')
        ->orderBy('program_name', 'asc')
        ->orderBy('id');
    }

    public function headings(): array
    {
        // Define headers for the exported file
        return [
            'id',
            'title_th',
            'first_name_th',
            'last_name_th',
            'title_en',
            'first_name_en',
            'last_name_en',
            'email',
            'phone',
            'classLevel',
            'level',
            'program_name',
            'test_center',
            'school',
            'school_sub_district',
            'school_district',
            'school_province',
            'payment_status',
            'payment_status_code',
        ];
    }
    public function chunkSize(): int
    {
        return 1000; // ปลอดภัยกับ WAMP
    }
    public function batchSize(): int
    {
        return 1000;
    }
    public function columnWidths(): array
    {
        return [
            'A' => 15,  // id
            'B' => 15,  // title_th
            'C' => 15,  // first_name_th
            'D' => 35,  // last_name_th
            'E' => 25,  // title_en
            'F' => 35,  // first_name_en
            'G' => 25,  // last_name_en
            'H' => 10,  // email
            'I' => 25,  // phone
            'J' => 10,  // classLevel
            'K' => 10,  // level
            'L' => 10,  // program_name
            'M' => 10,  // test_center
            'N' => 10,  // school
            'O' => 15,  // school_sub_district
            'P' => 15,  // school_district
            'Q' => 15,  // school_province
            'R' => 15,  // payment_status
            'S' => 15,  // payment_status_code
        ];
    }
    public function styles(Worksheet $sheet)
    {
        // ชิดซ้ายทั้งชีต (ตั้งแต่แถวที่ 2 ลงไป)
        $sheet->getStyle('A2:N' . $sheet->getHighestRow())
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_LEFT)
            ->setVertical(Alignment::VERTICAL_CENTER);

        return [
            1 => [ // แถวที่ 1 = header
                'font' => ['bold' => true],
                'alignment' => [
                    'horizontal' => 'center',
                    'vertical'   => 'center',
                    'wrapText'   => true,
                ],
            ],
        ];
    }
}