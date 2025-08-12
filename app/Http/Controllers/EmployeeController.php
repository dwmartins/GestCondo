<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    /**
     * Store a newly created employee and associate it with the selected condominium.
     *
     * @param  \App\Http\Requests\UserRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(UserRequest $request)
    {
        $condominiumId = $request->attributes->get('id_selected_condominium');

        $data = $request->validated();

        $user = new User($data);
        $user->password = Hash::make($data['password']);
        $user->condominium_id = $condominiumId;
        $user->save();

        $user->employee()->create([
            'occupation' => $data['employee']['occupation'],
            'admission_date' => $data['employee']['admission_date'],
            'employee_description' => $data['employee']['employee_description'],
            'status' => $data['employee']['status'],
        ]);

        return response()->json([
            'message' => 'Funcionário criado com sucesso.',
            'data' => $user
        ]);
    }
}
