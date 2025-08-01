<?php

namespace App\Http\Controllers;

use App\Http\Requests\CondominiumRequest;
use App\Models\Condominium;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CondominiumController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if(!in_array($user->role, ['suporte', 'sindico'])) {
            return response()->json(['message' => 'Acesso não autorizado.'], 403);
        }

        $condominiums = $user->role === 'suporte' ? Condominium::all() : $user->condominiums()->where('is_active', true)->get();

        return response()->json([
            'total' => $condominiums->count(),
            'data' => $condominiums
        ]);
    }

    public function store(CondominiumRequest $request)
    {
        $condominium = Condominium::create($request->validated());
        
        return response()->json([
            'message' => 'Condomínio criado com sucesso',
            'data' => $condominium
        ], 201);
    }

    public function show(string $id)
    {
        //
    }

    public function update(CondominiumRequest $request, string $id)
    {
        $condominium = Condominium::findOrFail($id);
        $condominium->update($request->validated());

        return response()->json([
            'message' => 'Condomínio atualizado com sucesso.',
            'data' => $condominium
        ]);
    }

    public function destroy(string $id)
    {
        $condominium = Condominium::find($id);

        if(!$condominium) {
            return response()->json([
                'message' => 'Condomínio não encontrado.'
            ], 404);
        }

        $condominium->delete();

        return response()->json([
            'message' => 'Condomínio excluído com sucesso.'
        ], 200);
    }

    public function updateStatus(Request $request, string $id)
    {
        $condominium = Condominium::find($id);

        if (!$condominium) {
            return response()->json(['message' => 'Condomínio não encontrado.'], 404);
        }

        if($request->filled('expires_at')) {
            $condominium->expires_at = Carbon::parse($request->input('expires_at'))->format('Y-m-d H:i:s');
        }

        $condominium->is_active = $request->input('is_active');
        $condominium->save();

        return response()->json([
            'message' => 'Alterações salvas com sucesso.',
            'data' => $condominium
        ]);
    }
}
