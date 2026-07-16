<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentTransfer extends Model
{
    use SoftDeletes;
    public $incrementing = false;
    protected $table = "student_transfer";

    protected $fillable = [
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
