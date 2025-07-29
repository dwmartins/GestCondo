<?php

use App\Http\Controllers\AuthController;
use App\Http\Middleware\ValidateSanctumTokenOrigin;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum', ValidateSanctumTokenOrigin::class])->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/validate-token', [AuthController::class, 'validateToken']);
    Route::patch('/auth/last-viewed-condominium', [AuthController::class, 'updateLastViewedCondominium']);
});