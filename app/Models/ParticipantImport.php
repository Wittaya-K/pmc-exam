<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ParticipantImport extends Model
{
    use SoftDeletes;
    public $incrementing = false;
    protected $table = "participant_import";

    protected $fillable = [
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
        'payment_status_code'
    ];
}
