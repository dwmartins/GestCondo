<?php

use App\Http\Controllers\EmployeeController;
use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\EnsureCondominiumAccess;
use App\Http\Middleware\ValidateSanctumTokenOrigin;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', ValidateSanctumTokenOrigin::class])->group(function() {
    Route::get('/employee', [EmployeeController::class, 'index'])->middleware([
        EnsureCondominiumAccess::class,
        CheckPermission::class . ':funcionario,visualizar'
    ]);

    Route::post('/employee', [EmployeeController::class, 'store'])->middleware([
        CheckPermission::class . ':funcionario,criar'
    ]);

    Route::put('/employee', [EmployeeController::class, 'update'])->middleware([
        CheckPermission::class . ':funcionario,editar'
    ]);

    Route::delete('/employee/{id}', [EmployeeController::class, 'destroy'])->middleware([
        CheckPermission::class . ':funcionario,excluir'
    ]);

    ROute::patch('/employee/{id}/change-settings', [EmployeeController::class, 'updateStatus'])->middleware([
        CheckPermission::class . ':funcionario.editar'
    ]);
});