<?php

use App\Http\Controllers\CommonSpaceController;
use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\EnsureCondominiumAccess;
use App\Http\Middleware\ValidateSanctumTokenOrigin;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', ValidateSanctumTokenOrigin::class, EnsureCondominiumAccess::class])->group(function() {
    Route::get('/common-spaces', [CommonSpaceController::class, 'index']);

    Route::post('/common-spaces', [CommonSpaceController::class, 'store'])->middleware([
        CheckPermission::class . ':espacosComuns,criar'
    ]);

    Route::put('/common-spaces', [CommonSpaceController::class, 'update'])->middleware([
        CheckPermission::class . ':espacosComuns,editar'
    ]);

    Route::delete('/common-spaces/{id}', [CommonSpaceController::class, 'destroy'])->middleware([
        CheckPermission::class . ':espacosComuns,excluir'
    ]);
});