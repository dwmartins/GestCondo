<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPermission extends Model
{
    protected $table = 'user_permissions';

    protected $fillable = ['user_id', 'permissions'];

    protected $casts = [
        'permissions' => 'array'
    ];

    public static function defaultPermissions()
    {
        return [
            'moradores' => [
                'label' => 'Moradores',
                'visualizar' => false,
                'criar' => false,
                'editar' => false,
                'excluir' => false,
            ],
            'funcionarios' => [
                'label' => 'Funcionarios',
                'visualizar' => false,
                'criar' => false,
                'editar' => false,
                'excluir' => false,
            ],
            'entregas' => [
                'label' => 'Entregas',
                'visualizar' => false,
                'criar' => false,
                'editar' => false,
                'excluir' => false,
            ],
            'espacosComuns' => [
                'label' => 'Espaços Comuns',
                'visualizar' => false,
                'criar' => false,
                'editar' => false,
                'excluir' => false,
            ]
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
