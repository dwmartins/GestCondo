<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommonSpace extends Model
{
    protected $table = 'common_spaces';

    protected $fillable = [
        'name',
        'description',
        'condominium_id',
        'rules',
        'manual_approval',
        'status'
    ];

    protected $appends = ['photo_url'];

    const PHOTO_PATH = 'images/common_space';

    protected $casts = [
        'rules' => 'array',
        'manual_approval' => 'boolean',
        'status' => 'boolean',
    ];

    public function reserves() {
        return $this->hasMany(Reserve::class, 'space_id');
    }

    public function condominium() {
        return $this->belongsTo(Condominium::class);
    }

    public function getPhotoUrlAttribute()
    {
        return $this->photo 
            ? asset("storage/" . self::PHOTO_PATH .  "/{$this->photo}") 
            : null;
    }
}
