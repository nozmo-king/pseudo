<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (!auth()->check()) {
        return file_get_contents(public_path('landing.html'));
    }
    return file_get_contents(public_path('index.html'));
});

Route::get('/{any}', function () {
    return file_get_contents(public_path('index.html'));
})->where('any', '.*');
