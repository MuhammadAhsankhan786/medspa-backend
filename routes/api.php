<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AppointmentController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| Protected Routes (JWT Auth Required)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:api')->group(function () {

    // 🔹 Auth actions
    Route::get('me', [AuthController::class, 'me']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);

    /*
    |----------------------------------------------------------------------
    | Role-based Dashboards
    |----------------------------------------------------------------------
    */
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/dashboard', fn () => response()->json(['message' => 'Welcome Admin']));
    });

    Route::middleware('role:provider')->prefix('provider')->group(function () {
        Route::get('/dashboard', fn () => response()->json(['message' => 'Welcome Provider']));
    });

    Route::middleware('role:reception')->prefix('reception')->group(function () {
        Route::get('/dashboard', fn () => response()->json(['message' => 'Welcome Reception']));
    });

    Route::middleware('role:client')->prefix('client')->group(function () {
        Route::get('/dashboard', fn () => response()->json(['message' => 'Welcome Client']));
    });

    Route::middleware('role:provider,reception')->prefix('staff')->group(function () {
        Route::get('/dashboard', fn () => response()->json(['message' => 'Welcome Staff']));
    });

    /*
    |----------------------------------------------------------------------
    | Appointments CRUD Routes (Role-Based)
    |----------------------------------------------------------------------
    */

    // Admin only
    Route::middleware('role:admin')->group(function() {
        Route::get('appointments', [AppointmentController::class, 'index']); // all appointments
        Route::patch('appointments/{appointment}/status', [AppointmentController::class, 'updateStatus']);
    });

    // Staff (Provider + Reception)
    Route::middleware('role:provider,reception')->group(function() {
        Route::get('appointments', [AppointmentController::class, 'index']); // only assigned appointments
        Route::patch('appointments/{appointment}/status', [AppointmentController::class, 'updateStatus']);
    });

    // Client only
    Route::middleware('role:client')->group(function() {
        Route::post('appointments', [AppointmentController::class, 'store']); // create appointment
        Route::delete('appointments/{appointment}', [AppointmentController::class, 'destroy']); // cancel
        Route::get('appointments/{appointment}', [AppointmentController::class, 'show']); // view own
    });
});
