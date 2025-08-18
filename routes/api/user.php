<?php

use App\Http\Controllers\UserController;
use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\EnsureCondominiumAccess;
use App\Http\Middleware\ValidateSanctumTokenOrigin;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', ValidateSanctumTokenOrigin::class])->group(function () {
    Route::post('/user', [UserController::class, 'store'])->middleware([
        EnsureCondominiumAccess::class,
        CheckPermission::class . ':moradores,criar'
    ]);
    
    Route::get('/user', [UserController::class, 'index'])->middleware([
        EnsureCondominiumAccess::class,
        CheckPermission::class . ':moradores,visualizar'
    ]);

    Route::patch('/user/{id}/status', [UserController::class, 'updateStatus'])->middleware([
        EnsureCondominiumAccess::class,
        CheckPermission::class . ':moradores,editar'
    ]);

    Route::get('/user/residents', [UserController::class, 'getUsersExceptSupportAndEmployee'])->middleware([
        EnsureCondominiumAccess::class,
        CheckPermission::class . ':moradores,visualizar'
    ]);

    Route::put('/user/{id}', [UserController::class, 'update'])->middleware([
        CheckPermission::class . ':moradores,editar'
    ]);

    Route::get('/user/{id}', [UserController::class, 'getById'])->middleware([
        CheckPermission::class . ':moradores,visualizar'
    ]);

    Route::post('/user/{id}/change-avatar', [UserController::class, 'changeAvatar'])->middleware([
        CheckPermission::class . ':moradores,editar'
    ]);

    Route::delete('/user/{id}', [UserController::class, 'destroy'])->middleware([
        CheckPermission::class . ':moradores,excluir'
    ]);

    Route::patch('/user/{id}/change-settings', [UserController::class, 'changeSettings'])->middleware([
        CheckPermission::class . ':moradores,editar'
    ]);
});
