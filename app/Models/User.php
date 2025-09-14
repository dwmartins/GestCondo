<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'condominium_id',
        'name',
        'last_name',
        'email',
        'role',
        'account_status',
        'description',
        'phone',
        'date_of_birth',
        'address',
        'complement',
        'city',
        'zip_code',
        'state',
        'country',
        'avatar',
        'accepts_emails',
        'last_login_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    const ROLE_SUPORTE = 'suporte';
    const ROLE_SINDICO = 'sindico';
    const ROLE_SUB_SINDICO = 'sub_sindico';
    const ROLE_MORADOR = 'morador';
    const ROLE_FUNCIONARIO = 'funcionario';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function updateLastLogin()
    {
        $this->timestamps = false;
        $this->last_login_at = now();
        $this->save();
        $this->timestamps = true;
    }

    public function updateLastViewedCondominium($condominiumId)
    {
        $this->timestamps = false;
        $this->last_viewed_condominium_id = $condominiumId;
        $this->save();
        $this->timestamps = true;
    }

    public function condominiums()
    {
        return $this->belongsToMany(Condominium::class, 'condominium_user');
    }

    public function permissions()
    {
        return $this->hasOne(UserPermission::class);
    }

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    public function isSuporte(): bool
    {
        return $this->role === self::ROLE_SUPORTE;
    }

    public function isSindico(): bool
    {
        return $this->role === self::ROLE_SINDICO;
    }

    public function isSubSindico(): bool
    {
        return $this->role === self::ROLE_SUB_SINDICO;
    }

    public function isMorador(): bool
    {
        return $this->role === self::ROLE_MORADOR;
    }

    public function getFullName(): string
    {
        return "$this->name $this->last_name";
    }
}
