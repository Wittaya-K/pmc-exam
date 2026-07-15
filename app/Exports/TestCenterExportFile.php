<?php

namespace App\Exports;

use App\Models\TestCenter;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
class TestCenterExportFile implements FromQuery, WithHeadings, WithChunkReading, WithColumnWidths, WithStyles
{

    public function query()
    {
        return TestCenter::select(
            'test_center',
            'building',
            'floor',
            'room',
            'capacity',
            'session',
            'air_condition',
            'fan'
        )
            ->orderBy('id');
    }

    public function headings(): array
    {
        // Define headers for the exported file
        return [
            'test_center',
            'building',
            'floor',
            'room',
            'capacity',
            'session',
            'air_condition',
            'fan'
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
            'A' => 40,  // test_center
            'B' => 35,  // building
            'C' => 15,  // floor
            'D' => 25,  // room
            'E' => 15,  // capacity
            'F' => 15,  // session
            'G' => 15,  // air condition
            'H' => 15,  // fan
        ];
    }
    public function styles(Worksheet $sheet)
    {
        // ชิดซ้ายทั้งชีต (ตั้งแต่แถวที่ 2 ลงไป)
        $sheet->getStyle('A2:H' . $sheet->getHighestRow())
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
