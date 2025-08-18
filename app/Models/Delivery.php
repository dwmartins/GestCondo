<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'condominium_id',
        'user_id',
        'employee_id',
        'package_description',
        'status',
        'received_at',
        'delivered_to_name',
        'delivered_at',
        'notes',
    ];

     /**
     * Relationship with the condominium
     */
    public function condominium()
    {
        return $this->belongsTo(Condominium::class);
    }

    /**
     * Relationship with the resident (user who will receive the delivery)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship with the gatekeeper who registered/delivered
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
