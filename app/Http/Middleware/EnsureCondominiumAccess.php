<?php

namespace App\Http\Middleware;

use App\Models\Condominium;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCondominiumAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $condominiumId = $request->header('X-Condominium-Id');

        if (!$condominiumId) {
            return response()->json([
                'message' => 'O cabeçalho "X-Condominium-Id" é obrigatório para realizar esta operação.'
            ], 422);
        }

        $condominiumExists = Condominium::where('id', $condominiumId)->exists();

        if (!$condominiumExists) {
            return response()->json([
                'message' => 'O condomínio informado não existe ou está inativo.'
            ], 422);
        }

        $request->attributes->set('id_selected_condominium', $condominiumId);

        $user = $request->user();

        if ($user->role === User::ROLE_SUPORTE) {
            return $next($request);
        }

        $hasAccess = null;

        if($user->role === User::ROLE_SINDICO) {
            $hasAccess = $user->condominiums()
                ->where('condominiums.id', $condominiumId)
                ->where('is_active', true)
                ->exists();
        } else {
            $hasAccess = Condominium::where('id', $user->condominium_id)
                ->where('is_active', true)
                ->exists();
        }

        if (!$hasAccess) {
            return response()->json([
                'message' => 'Você não tem permissão para acessar este condomínio.'
            ], 403);
        }

        return $next($request);
    }
}
