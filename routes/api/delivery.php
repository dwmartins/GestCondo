<?php

use App\Http\Controllers\DeliveryController;
use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\EnsureCondominiumAccess;
use App\Http\Middleware\ValidateSanctumTokenOrigin;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', ValidateSanctumTokenOrigin::class])->group(function() {
    Route::get('/delivery', [DeliveryController::class, 'index'])->middleware([
        EnsureCondominiumAccess::class,
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
});