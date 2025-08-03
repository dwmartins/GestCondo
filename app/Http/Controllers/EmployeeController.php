<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    /**
     * get All employees
     * 
     * @return \Illuminate\Http\JsonResponse 
     */
    public function index(Request $request)
    {
        $condominiumId = $request->attributes->get('id_selected_condominium');

        $employees = Employee::query()
            ->where('condominium_id', $condominiumId)
            ->get();

        return response()->json([
            'total' => $employees->count(),
            'data' => $employees
        ]);
    }
}
