<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticationController;
use App\Http\Controllers\API\UserController;


Route::post('/login', [AuthenticationController::class, 'login']);
Route::post('/register', [AuthenticationController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthenticationController::class, 'logout']);
    Route::post('/users/{user}/suspend', [UserController::class, 'suspend']);
    Route::post('/users/{user}/activate', [UserController::class, 'activate']);
    Route::resource('users', UserController::class);
});