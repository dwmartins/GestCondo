<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\Employee;
use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

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

    /**
     * Update Employee
     *
     * @param  \App\Http\Requests\UserRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UserRequest $request)
    {
        $data = $request->validated();

        $user = User::find($data['id']);

        if(!$user) {
            return response()->json([
                'message' => 'Funcionário não encontrado.'
            ], 404);
        }

        $user->update($data);

        if(!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
            $user->save();
        }

        $employee = Employee::find($data['employee']['id']);
        $employee->update($data['employee']);

        $user->load('employee');

        return response()->json([
            'message' => 'Funcionário atualizado com sucesso.',
            'data' => $user
        ]);
    }

    /**
     * Delete Employee
     *
     * @param  \App\Http\Requests\UserRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $id)
    {
        $user = User::find($id);

        if(!$user) {
            return response()->json([
                'message' => 'Funcionário não encontrado.'
            ], 404);
        }

        if($user->avatar) {
            Storage::disk('public')->delete('avatars/' . $user->avatar);
        }

        $user->delete();

        return response()->json([
            'message' => 'Funcionário excluído com sucesso.'
        ]);
    }
}
