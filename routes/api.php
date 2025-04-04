<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DaroodCounterController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    try {
        $user = $request->user(); // Get authenticated user
        $books = \App\Models\Book::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'books' => $books
        ], 200);
    } catch (\Exception $e) {
        return response()->json(['message' => 'Internal server error'], 500);
    }
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->get('/books/user', [BookController::class, 'userBooks']);

Route::middleware('auth:sanctum')->get('/daroodCount/user', [DaroodCounterController::class, 'userCounts']);
Route::middleware('auth:sanctum')->get('/daroodCount', [DaroodCounterController::class, 'index']);
Route::middleware('auth:sanctum')->post('/daroodCount/store', [DaroodCounterController::class, 'store']);
Route::middleware('auth:sanctum')->put('/daroodCount/{count}', [DaroodCounterController::class, 'update']);
Route::middleware('auth:sanctum')->delete('/daroodCount/{count}', [DaroodCounterController::class, 'destroy']);
Route::middleware('auth:sanctum')->get('/daroodCount/{count}', [DaroodCounterController::class, 'show']);


Route::apiResource('books', BookController::class);

Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/login', [AuthController::class, 'login']);
Route::post('auth/forgetPass', [AuthController::class, 'forgetPass']);
Route::post('auth/resetPassword', [AuthController::class, 'resetPassword']);

Route::post('auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
