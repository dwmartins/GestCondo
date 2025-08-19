<?php

use App\Http\Middleware\CheckApiKey;
use Illuminate\Support\Facades\Route;

Route::middleware([CheckApiKey::class])->group(function () {
    require base_path('routes/api/auth.php');
    require base_path('routes/api/user.php');
    require base_path('routes/api/condominium.php');
    require base_path('routes/api/employee.php');
    require base_path('routes/api/delivery.php');
});

