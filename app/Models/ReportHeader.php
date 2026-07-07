<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReportHeader extends Model
{
    use SoftDeletes;
    public $incrementing = false;
    protected $table = "report_header";

    protected $fillable = [
        'project_name_th',
        'project_name_en',
        'exam_date_open'
    ];
}
