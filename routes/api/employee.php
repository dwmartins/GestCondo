<?php

use App\Http\Controllers\EmployeeController;
use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\EnsureCondominiumAccess;
use App\Http\Middleware\ValidateSanctumTokenOrigin;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', ValidateSanctumTokenOrigin::class])->group(function() {
    Route::post('/employee', [EmployeeController::class, 'store'])->middleware([
        EnsureCondominiumAccess::class,
        CheckPermission::class . ':funcionario,criar'
    ]);
});