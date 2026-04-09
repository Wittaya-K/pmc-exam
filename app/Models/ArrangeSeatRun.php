<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArrangeSeatRun extends Model
{
    protected $table = 'arrange_seat_runs';

    protected $fillable = [
        'status',
        'created_by_user_id',
        'started_at',
        'finished_at',
        'error',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];
}

