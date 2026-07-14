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
use App\Http\Controllers\API\ReservationCancellationController;
use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\API\WebhookController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::post('/login', [AuthenticationController::class, 'login']);
Route::post('/register', [AuthenticationController::class, 'register']);

Route::get('/movies/{movie}', [MovieController::class, 'show']);

Route::get('/cinemas', [CinemaController::class, 'index']);
Route::get('/cinemas/{cinema}', [CinemaController::class, 'show']);

Route::get('/showtimes', [ShowtimeController::class, 'index']);
Route::get('/showtimes/{showtime}', [ShowtimeController::class, 'show']);
Route::get('/showtimes/{showtime}/available-seats', [ShowtimeSeatController::class, 'availableSeats']);

Route::get('/halls/{hall}/seats', [SeatController::class, 'index']);

Route::post('/stripe/webhook', [WebhookController::class, 'handle']);

/*
|--------------------------------------------------------------------------
| Protected Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthenticationController::class, 'logout']);

    // Users
    Route::post('/users/{user}/suspend', [UserController::class, 'suspend']);
    Route::post('/users/{user}/activate', [UserController::class, 'activate']);
    Route::resource('users', UserController::class);

    // Movies
    Route::post('/movies', [MovieController::class, 'store']);
    Route::put('/movies/{movie}', [MovieController::class, 'update']);
    Route::patch('/movies/{movie}', [MovieController::class, 'update']);
    Route::delete('/movies/{movie}', [MovieController::class, 'destroy']);

    // Cinemas
    Route::put('/cinemas/{cinema}', [CinemaController::class, 'update']);
    Route::patch('/cinemas/{cinema}', [CinemaController::class, 'update']);

    // Halls
    Route::resource('halls', HallController::class)->except(['index', 'show', 'destroy']);

    // Seats
    Route::get('/seats/{seat}', [SeatController::class, 'show']);
    Route::put('/seats/{seat}', [SeatController::class, 'update']);

    // Showtimes
    Route::post('/showtimes', [ShowtimeController::class, 'store']);
    Route::put('/showtimes/{showtime}', [ShowtimeController::class, 'update']);
    Route::patch('/showtimes/{showtime}', [ShowtimeController::class, 'update']);
    Route::delete('/showtimes/{showtime}', [ShowtimeController::class, 'destroy']);

    // Reservations
    Route::resource('reservations', ReservationController::class)->except(['update', 'destroy']);
    Route::post('/reservations/{reservation}/cancel', [ReservationCancellationController::class, 'cancel']);

    // Payments
    Route::post('/payments/{reservation}', [PaymentController::class, 'pay']);
});
