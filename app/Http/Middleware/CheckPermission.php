<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $module, string $action): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Não autenticado'], 401);
        }

        if (!$this->checkUserPermission($user, $module, $action)) {
            return response()->json(['message' => 'Acesso não autorizado'], 403);
        }

        return $next($request);
    }

    /**
     * Check user permissions
     */
    protected function checkUserPermission(User $user, string $module, string $action): bool
    {
        if (in_array($user->role, [User::ROLE_SINDICO, User::ROLE_SUPORTE])) {
            return true;
        }

        if (!$user->permissions) {
            return false;
        }

        $permissions = $user->permissions->permissions;

        return $permissions[$module][$action] ?? false;
    }
}
