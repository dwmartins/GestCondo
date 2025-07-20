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
        return response()->json($condominium, 201);
    }

    public function show(string $id)
    {
        //
    }

    public function update(Request $request, string $id)
    {
        //
    }

    public function destroy(string $id)
    {
        //
    }
}
