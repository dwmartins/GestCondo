<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';

    protected $fillable = [
        'user_id',
        'condominium_id',
        'type',
        'title',
        'message',
        'related_id',
        'is_read',
    ];

    const TYPE_ENTREGA = 'entrega';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function condominium()
    {
        return $this->belongsTo(Condominium::class);
    }
}
