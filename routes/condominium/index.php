<?php

use App\Http\Controllers\CondominiumController;
use App\Http\Middleware\EnsureUserIsSupport;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', EnsureUserIsSupport::class])->group(function() {
    Route::post('/condominium', [CondominiumController::class, 'store']);
    Route::get('/condominium', [CondominiumController::class, 'index']);
    Route::put('/condominium/{id}', [CondominiumController::class, 'update']);
    Route::delete('/condominium/{id}', [CondominiumController::class, 'destroy']);
    Route::patch('/condominium/{id}/status', [CondominiumController::class, 'updateStatus']);
});