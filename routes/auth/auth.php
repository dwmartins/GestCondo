<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/auth/logout', [AuthController::class, 'logout']);
Route::middleware('auth:sanctum')->get('/auth/validate-token', [AuthController::class, 'validateToken']);
Route::middleware('auth:sanctum')->patch('/auth/last-viewed-condominium', [AuthController::class, 'updateLastViewedCondominium']);