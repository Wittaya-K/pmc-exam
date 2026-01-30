<?php

namespace App\Imports;

use App\Models\TestCenter;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithBatchInserts;

class CenterImportFile implements ToModel, WithStartRow, WithChunkReading, WithBatchInserts
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {

        return new TestCenter([
            'test_center'   => $row[0] ?? null,
            'building'      => $row[1] ?? null,
            'floor'         => $row[2] ?? null,
            'room'          => $row[3] ?? null,
            'capacity'      => $row[4] ?? null,
            'session'       => $row[5] ?? null,
            'air_condition' => $row[6] ?? null,
            'fan'           => $row[7] ?? null,
        ]);
    }

    public function startRow(): int
    {
        return 2; // Skip the first row
    }
    public function chunkSize(): int
    {
        return 1000; // ปลอดภัยกับ WAMP
    }
    public function batchSize(): int
    {
        return 1000;
    }
}