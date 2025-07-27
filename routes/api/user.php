<?php

use App\Http\Controllers\UserController;
use App\Http\Middleware\EnsureUserCanManageUsers;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/user', [UserController::class, 'store'])->middleware(EnsureUserCanManageUsers::class);
    Route::get('/user', [UserController::class, 'index'])->middleware(EnsureUserCanManageUsers::class);
});
