<?php

use App\Http\Controllers\EmployeeController;
use App\Http\Middleware\EnsureCondominiumAccess;
use App\Http\Middleware\EnsureUserCanManageUsers;
use App\Http\Middleware\ValidateSanctumTokenOrigin;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', ValidateSanctumTokenOrigin::class])->group(function () {
    Route::get('/employee', [EmployeeController::class, 'index'])->middleware([
        EnsureCondominiumAccess::class,
        EnsureUserCanManageUsers::class
    ]);
});