<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $table = 'audit_logs';

    protected $fillable = [
        'user_id',
        'user_name',
        'condominium_id',
        'action',
        'description',
        'changes'
    ];

    protected $casts = [
        'changes' => 'array'
    ];

    // ===================================
    // Action constants for residents
    // ===================================
    const ADD_RESIDENT     = 'adicionou o morador';
    const UPDATED_RESIDENT = 'editou o morador';
    const DELETED_RESIDENT = 'excluiu o morador';

    // ===================================
    // Action constants for deliveries
    // ===================================
    const ADD_DELIVERY          = 'registrou a entrega';
    const UPDATED_DELIVERY      = 'editou registro de entrega';
    const DELETED_DELIVERY      = 'excluiu registro de entrega';
    const CONFIRMED_DELIVERY    = 'confirmou recebimento da entrega'; 

    // ===================================
    // Action constants for Common spaces
    // ===================================
    const ADD_COMMON_SPACE      = 'adicionou o espaço comum';
    const UPDATED_COMMON_SPACE  = 'editou o espaço comum';
    const DELETED_COMMON_SPACE  = 'excluiu a área comum';
 
    /**
     * @param User $user                  User who performed the action
     * @param int $condominiumId          Related condominium
     * @param string $action              Action constant (ADD/UPDATE/DELETE)
     * @param string|null $targetName     Name of affected delivery
     * @param array|null $changes         Changes made
     */
    public static function residentLog(User $user, int $condominium_id, string $action, ?string $targetName = null, ?array $changes = null)
    {
        if($user->isSuporte()) return;

        $description = $targetName
            ? "{$user->name} {$action} ({$targetName})"
            : "{$user->name} {$action}";

        return self::create([
            'user_id'        => $user->id,
            'user_name'      => $user->name,
            'condominium_id' => $condominium_id,
            'action'         => ucfirst($action),
            'description'    => $description,
            'changes'        => $changes,
        ]);
    }

    /**
     * @param User $user                  User who performed the action
     * @param int $condominiumId          Related condominium
     * @param string $action              Action constant (ADD/UPDATE/DELETE)
     * @param string|null $targetName     Name of affected item
     * @param array|null $changes         Changes made
     */
    public static function deliveryLog(User $user, int $condominium_id, string $action, ?string $itemName = null, ?array $changes = null)
    {
        if($user->isSuporte()) return;

        $description = "{$user->name} {$action} ({$itemName})";

        if($action === self::CONFIRMED_DELIVERY) {
            $description = "{$user->name} confirmou o recebimento da entrega.";
        }

        return self::create([
            'user_id'        => $user->id,
            'user_name'      => $user->name,
            'condominium_id' => $condominium_id,
            'action'         => ucfirst($action),
            'description'    => $description,
            'changes'        => $changes,
        ]);
    }

    /**
     * @param User $user                  User who performed the action
     * @param int $condominiumId          Related condominium
     * @param string $action              Action constant (ADD/UPDATE/DELETE)
     * @param string|null $targetName     Name of affected item
     * @param array|null $changes         Changes made
     */
    public static function commonSpaceLog(User $user, int $condominium_id, string $action, ?string $itemName = null, ?array $changes = null) 
    {
        if($user->isSuporte()) return;

        $description = "{$user->name} {$action} ({$itemName})";

        return self::create([
            'user_id'        => $user->id,
            'user_name'      => $user->name,
            'condominium_id' => $condominium_id,
            'action'         => ucfirst($action),
            'description'    => $description,
            'changes'        => $changes,
        ]);
    }
}
