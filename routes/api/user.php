<?php

use App\Http\Controllers\UserController;
use App\Http\Middleware\EnsureCondominiumAccess;
use App\Http\Middleware\EnsureUserCanManageUsers;
use App\Http\Middleware\ValidateSanctumTokenOrigin;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', ValidateSanctumTokenOrigin::class])->group(function () {
    Route::post('/user', [UserController::class, 'store'])->middleware([
        EnsureCondominiumAccess::class,
        EnsureUserCanManageUsers::class
    ]);
    
    Route::get('/user', [UserController::class, 'index'])->middleware([
        EnsureCondominiumAccess::class,
        EnsureUserCanManageUsers::class
    ]);

    Route::delete('/user/{id}', [UserController::class, 'destroy'])->middleware([
        EnsureCondominiumAccess::class,
        EnsureUserCanManageUsers::class
    ]);

    Route::patch('/user/{id}/status', [UserController::class, 'updateStatus'])->middleware([
        EnsureCondominiumAccess::class,
        EnsureUserCanManageUsers::class
    ]);

    Route::get('/user/{id}', [UserController::class, 'getById']);
    Route::put('/user/{id}', [UserController::class, 'update']);
    Route::post('/user/{id}/change-avatar', [UserController::class, 'changeAvatar']);
});
