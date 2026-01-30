<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\CenterImportFile;
use Illuminate\Support\Facades\Log;

class TestCenterJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $filePaths;
    public $timeout = 0;

    public function __construct($filePaths)
    {
        $this->filePaths = $filePaths;
    }

    public function handle()
    {
        // sort ตามตัวเลขหน้าชื่อไฟล์
        usort($this->filePaths, function ($a, $b) {
            preg_match('/^\d+/', $a['original_name'], $ma);
            preg_match('/^\d+/', $b['original_name'], $mb);

            return intval($ma[0] ?? 0) <=> intval($mb[0] ?? 0);
        });

        foreach ($this->filePaths as $fileInfo) {
            $path = $fileInfo['path'];
            
            // Import ไฟล์
            Excel::import(new CenterImportFile, $path);

            // ลบไฟล์หลัง import เสร็จ
            if (file_exists($path)) {
                unlink($path);
            }
        }

        Log::info('Processing completed for ' . count($this->filePaths) . ' files');
    }
}