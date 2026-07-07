<?php

namespace App\Imports;

use App\Models\ParticipantImport;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithBatchInserts;

class ParticipantImportFile implements ToModel, WithStartRow, WithChunkReading, WithBatchInserts

{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {

        return new ParticipantImport([
            'id'                    => $row[0] ?? null,
            'title_th'              => $row[1] ?? null,
            'first_name_th'         => $row[2] ?? null,
            'last_name_th'          => $row[3] ?? null,
            'title_en'              => $row[4] ?? null,
            'first_name_en'         => $row[5] ?? null,
            'last_name_en'          => $row[6] ?? null,
            'email'                 => $row[7] ?? null,
            'phone'                 => $row[8] ?? null,
            'classLevel'            => $row[9] ?? null,
            'level'                 => $row[10] ?? null,
            'program_name'          => $row[11] ?? null,
            // 'test_center'           => preg_replace('/\s+/', '', $row[12]) ?? null,
            'test_center'           => $row[12] ?? null,
            'school'                => $row[13] ?? null,
            'school_sub_district'   => $row[14] ?? null,
            'school_district'       => $row[15] ?? null,
            'school_province'       => $row[16] ?? null,
            'payment_status'        => $row[17] ?? null,
            'payment_status_code'   => $row[18] ?? null,
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
