<?php

namespace App\Exports;

use App\Models\SeatAssign;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
class StudentRecheckExportFile implements FromQuery, WithHeadings, WithChunkReading,WithColumnWidths,WithStyles
{

    public function query()
    {
        return SeatAssign::select(
            'id',
            'first_name_th',
            'last_name_th',
            'school',
            'program_name',
            'test_center',
            'classLevel',
            'level',
            'build_floor_room',
            'building',
            'floor',
            'room',
            'session',
            'seat_no',
            'attendance_status',
            'absence_reason',
            'checked_at',
            'checked_by'
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
            'first_name_th',
            'last_name_th',
            'school',
            'program_name',
            'test_center',
            'classLevel',
            'level',
            'build_floor_room',
            'building',
            'floor',
            'room',
            'session',
            'seat_no',
            'attendance_status',
            'absence_reason',
            'checked_at',
            'checked_by'
        ];
    }
    public function chunkSize(): int
    {
        return 1000; // ปลอดภัยกับ WAMP
    }
    public function columnWidths(): array
    {
        return [
            'A' => 15,  // id
            'B' => 15,  // first_name_th
            'C' => 15,  // last_name_th
            'D' => 30,  // school
            'E' => 20,  // program_name
            'F' => 40,  // test_center
            'G' => 20,  // classLevel
            'H' => 15,  // level
            'I' => 70,  // build_floor_room
            'J' => 45,  // building
            'K' => 15,  // floor
            'L' => 15,  // room
            'M' => 15,  // session
            'N' => 15,  // seat_no
            'O' => 15,  // attendance_status
            'P' => 15,  // absence_reason
            'Q' => 15,  // checked_at
            'R' => 15,  // checked_by
        ];
    }
    public function styles(Worksheet $sheet)
    {
        // ชิดซ้ายทั้งชีต (ตั้งแต่แถวที่ 2 ลงไป)
        $sheet->getStyle('A2:R' . $sheet->getHighestRow())
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