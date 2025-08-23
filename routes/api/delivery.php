<?php

use App\Http\Controllers\DeliveryController;
use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\EnsureCondominiumAccess;
use App\Http\Middleware\ValidateSanctumTokenOrigin;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', ValidateSanctumTokenOrigin::class, EnsureCondominiumAccess::class])->group(function() {
    Route::get('/delivery', [DeliveryController::class, 'index'])->middleware([
        CheckPermission::class . ':entregas,visualizar'
    ]);

    Route::get('/delivery/{id}', [DeliveryController::class, 'getById'])->middleware([
        CheckPermission::class . ':entregas,visualizar'
    ]);

    Route::post('/delivery', [DeliveryController::class, 'store'])->middleware([
        CheckPermission::class . ':entregas,criar'
    ]);

    Route::put('/delivery', [DeliveryController::class, 'update'])->middleware([
        CheckPermission::class . ':entregas,atualizar'
    ]);

    Route::delete('/delivery/{id}', [DeliveryController::class, 'destroy'])->middleware([
        CheckPermission::class . ':entregas,excluir'
    ]);
});