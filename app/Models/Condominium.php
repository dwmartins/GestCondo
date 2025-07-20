<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Condominium extends Model
{
    use HasFactory;

    protected $table = 'condominiums';

    protected $fillable = [
        'name',
        'cnpj',
        'company_type',
        'postal_code',
        'street',
        'number',
        'neighborhood',
        'city',
        'state',
        'phone',
        'email',
    ];
}
