<?php

use App\Http\Controllers\CondominiumController;
use Illuminate\Support\Facades\Route;

Route::post('/condominium', [CondominiumController::class, 'store']);