<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::controller(BookController::class)->group(function () {
    Route::get('books/user', 'userBooks')->middleware('auth:sanctum');
});

Route::apiResource('books', BookController::class);

Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/login', [AuthController::class, 'login']);

Route::post('auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
