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

        $user = $request->user();

        // Support has access to everything
        if ($user->role === User::ROLE_SUPORTE) {
            $request->attributes->set('id_selected_condominium', $condominiumId);
            return $next($request);
        }

        $condominium = Condominium::where('id', $condominiumId)
            ->where('is_active', true)
            ->first();

        if (!$condominium) {
            return response()->json([
                'message' => 'O condomínio informado não existe ou está inativo.'
            ], 422);
        }

        $request->attributes->set('id_selected_condominium', $condominium->id);
        $hasAccess = false;

        if ($user->role === User::ROLE_SINDICO) {
            $hasAccess = $user->condominiums()
                ->where('condominiums.id', $condominiumId)
                ->exists();
        } else {
            // Resident
            $hasAccess = $user->condominium_id === (int) $condominiumId;
        }

        if (!$hasAccess) {
            return response()->json([
                'message' => 'Você não tem permissão para acessar este condomínio.'
            ], 403);
        }

        return $next($request);
    }
}
