<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TestCenter extends Model
{
    use SoftDeletes;
    public $incrementing = false;
    protected $table = "test_center";

    protected $fillable = [
        'id',
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
