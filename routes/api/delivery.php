<?php

use App\Http\Controllers\DeliveryController;
use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\ValidateSanctumTokenOrigin;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', ValidateSanctumTokenOrigin::class])->group(function() {
    Route::post('/delivery', [DeliveryController::class, 'store'])->middleware([
        CheckPermission::class . ':entregas,criar'
    ]);
});