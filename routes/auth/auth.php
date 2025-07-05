<?php

use Illuminate\Support\Facades\Route;

Route::get('/auth/login', function() {
    return [
        "status" => "OK",
        "message" => "foi"
    ];
});