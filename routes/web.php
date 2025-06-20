<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('profit-optimizer');
});

Route::get('/profit-optimizer', function () {
    return view('profit-optimizer');
});