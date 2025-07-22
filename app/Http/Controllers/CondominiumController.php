<?php

namespace App\Http\Controllers;

use App\Http\Requests\CondominiumRequest;
use App\Models\Condominium;
use Illuminate\Http\Request;

class CondominiumController extends Controller
{
    public function index()
    {
        $condominiums = Condominium::all();

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

        return response()->json(['message' => 'Condomínio atualizado com sucesso.']);
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
}
