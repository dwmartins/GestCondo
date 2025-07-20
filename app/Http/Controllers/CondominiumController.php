<?php

namespace App\Http\Controllers;

use App\Http\Requests\CondominiumRequest;
use App\Models\Condominium;
use Illuminate\Http\Request;

class CondominiumController extends Controller
{
    public function index()
    {
        //
    }

    public function store(CondominiumRequest $request)
    {
        $condominium = Condominium::create($request->validate());
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
