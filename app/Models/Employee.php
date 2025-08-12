<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'user_id',
        'occupation',
        'admission_date',
        'resignation_date',
        'employee_description',
        'status',
    ];

    const STATUS_TRABALHANDO = 'trabalhando';
    const STATUS_FERIAS = 'ferias';
    const STATUS_LICENCA = 'licenca';
    const STATUS_AFASTADO = 'afastado';
    const STATUS_DESLIGADO = 'desligado';
    const STATUS_SUSPENSO = 'suspenso';

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
