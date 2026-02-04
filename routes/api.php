<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticationController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\MovieController;
use App\Http\Controllers\API\CinemaController;
use App\Http\Controllers\API\HallController;
use App\Http\Controllers\API\SeatController;
use App\Http\Controllers\API\ShowtimeController;
use App\Http\Controllers\API\ReservationController;
use App\Http\Controllers\API\ShowtimeSeatController;

Route::post('/login', [AuthenticationController::class, 'login']);
Route::post('/register', [AuthenticationController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthenticationController::class, 'logout']);
    Route::post('/users/{user}/suspend', [UserController::class, 'suspend']);
    Route::post('/users/{user}/activate', [UserController::class, 'activate']);
    Route::resource('users', UserController::class);
    Route::resource('movies', MovieController::class);
    Route::resource('cinemas', CinemaController::class)->only(['show', 'update']);
    Route::resource('halls', HallController::class)->except(['destroy']);
    // List all seats of a hall
    Route::get('/halls/{hall}/seats', [SeatController::class, 'index']);
    // View a single seat (by seat id)
    Route::get('/seats/{seat}', [SeatController::class, 'show']);
    // Update a seat (type / status)
    Route::put('/seats/{seat}', [SeatController::class, 'update']);

    Route::get('/showtimes/{showtime}/available-seats', [ShowtimeSeatController::class, 'availableSeats']);
    Route::resource('showtimes', ShowtimeController::class);
    Route::resource('reservations', ReservationController::class);
});