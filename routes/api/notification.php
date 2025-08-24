<?php

use App\Http\Controllers\NotificationController;
use App\Http\Middleware\EnsureCondominiumAccess;
use App\Http\Middleware\ValidateSanctumTokenOrigin;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', ValidateSanctumTokenOrigin::class, EnsureCondominiumAccess::class])->group(function() {
    Route::get('/notification', [NotificationController::class, 'index']);
    Route::put('/notification/{id}/mark-read', [NotificationController::class, 'markAsRead']);
    Route::post('/notification/mark-all-read', [NotificationController::class, 'markAllAsRead']);
});