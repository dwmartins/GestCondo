<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\Employee;
use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    /**
     * Get all Employees
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $condominiumId = $request->attributes->get('id_selected_condominium');

        $employees = User::with('employee')
            ->where('condominium_id', $condominiumId)
            ->where('role', 'funcionario')
            ->whereHas('employee')
            ->get();

        return response()->json($employees);
    }

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

        $user->permissions()->create([
            'user_id' => $user->id,
            'permissions' => $data['permissions'] ?? UserPermission::defaultPermissions()
        ]);

        $user->load('employee', 'permissions');

        return response()->json([
            'message' => 'Funcionário criado com sucesso.',
            'data' => $user
        ]);
    }
}
