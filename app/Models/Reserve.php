<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reserve extends Model
{
    protected $table = 'reserves';

    protected $fillable = [
        'space_id',
        'user_id',
        'date',
        'start_time',
        'end_time',
        'status',
        'reason'
    ];

    protected $casts = [
        'data' => 'date',
    ];

    public function espaco() {
        return $this->belongsTo(CommonSpace::class, 'space_id');
    }


    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }
}
