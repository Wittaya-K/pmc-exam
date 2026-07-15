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
class ParticipantExportFile implements FromQuery, WithHeadings, WithChunkReading, WithColumnWidths, WithStyles
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
            'seat_no'
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
            'seat_no'
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
            'B' => 15,  // first_name_th
            'C' => 15,  // last_name_th
            'D' => 35,  // school
            'E' => 25,  // program_name
            'F' => 35,  // test_center
            'G' => 25,  // classLevel
            'H' => 10,  // level
            'I' => 25,  // build_floor_room
            'J' => 10,  // build
            'K' => 10,  // floor
            'L' => 10,  // room
            'M' => 10,  // session
            'N' => 10,  // seat_no
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
                    'vertical' => 'center',
                    'wrapText' => true,
                ],
            ],
        ];
    }
}
