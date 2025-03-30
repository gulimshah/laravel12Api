<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get(
    '/test',
    function () {
        return "Not authorized, please login first or provided Token is wrong";
    }
)->name('login');
