<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    const STATUS_ACTIVE = 'active';
    const STATUS_ON_LEAVE = 'on_leave';
    const STATUS_FIRED = 'fired';
    const STATUS_SUSPENDED = 'suspended';
    const STATUS_VACATION = 'vacation';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'position',
        'schedule',
        'admission_date',
        'termination_date',
        'status',
        'observations'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'schedule' => 'array',
        'admission_date' => 'date',
        'termination_date' => 'date',
    ];

    /**
     * Get all available statuses with their labels.
     *
     * @return array
     */
    public static function getStatusOptions(): array
    {
        return [
            self::STATUS_ACTIVE => 'Ativo',
            self::STATUS_ON_LEAVE => 'Afastado',
            self::STATUS_FIRED => 'Demitido',
            self::STATUS_SUSPENDED => 'Suspenso',
            self::STATUS_VACATION => 'Férias',
        ];
    }

    /**
     * Scope a query to only include active employees.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Check if employee is active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }
}
