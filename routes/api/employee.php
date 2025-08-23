<?php

use App\Http\Controllers\EmployeeController;
use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\EnsureCondominiumAccess;
use App\Http\Middleware\ValidateSanctumTokenOrigin;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', ValidateSanctumTokenOrigin::class, EnsureCondominiumAccess::class])->group(function() {
    Route::get('/employee', [EmployeeController::class, 'index'])->middleware([
        CheckPermission::class . ':funcionarios,visualizar'
    ]);

    Route::get('/employee/{id}', [EmployeeController::class, 'getById'])->middleware([
       CheckPermission::class . ':funcionarios,visualizar' 
    ]);

    Route::post('/employee', [EmployeeController::class, 'store'])->middleware([
        CheckPermission::class . ':funcionarios,criar'
    ]);

    Route::put('/employee', [EmployeeController::class, 'update'])->middleware([
        CheckPermission::class . ':funcionarios,editar'
    ]);

    Route::delete('/employee/{id}', [EmployeeController::class, 'destroy'])->middleware([
        CheckPermission::class . ':funcionarios,excluir'
    ]);

    ROute::patch('/employee/{id}/change-settings', [EmployeeController::class, 'updateStatus'])->middleware([
        CheckPermission::class . ':funcionarios,editar'
    ]);
});