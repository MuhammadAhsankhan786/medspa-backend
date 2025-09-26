<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AppointmentController;

/*
|------------------------------------------------------------------
| Public Routes
|------------------------------------------------------------------
*/
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

/*
|------------------------------------------------------------------
| Protected Routes (JWT Auth Required)
|------------------------------------------------------------------
*/
Route::middleware('auth:api')->group(function () {

    // 🔹 Auth actions
    Route::get('me', [AuthController::class, 'me']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);

    /*
    |------------------------------------------------------------------
    | Admin routes
    |------------------------------------------------------------------
    */
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('appointments', [AppointmentController::class, 'index']);
        Route::patch('appointments/{appointment}/status', [AppointmentController::class, 'updateStatus']);
    });

    /*
    |------------------------------------------------------------------
    | Staff (provider + reception) routes
    |------------------------------------------------------------------
    */
    Route::middleware('role:provider,reception')->prefix('staff')->group(function () {
        Route::get('appointments', [AppointmentController::class, 'index']);
        Route::patch('appointments/{appointment}/status', [AppointmentController::class, 'updateStatus']);
    });

    /*
    |------------------------------------------------------------------
    | Client routes
    |------------------------------------------------------------------
    */
    Route::middleware('role:client')->prefix('client')->group(function () {
        Route::post('appointments', [AppointmentController::class, 'store']);
        Route::delete('appointments/{appointment}', [AppointmentController::class, 'destroy']);
        Route::get('appointments/{appointment}', [AppointmentController::class, 'show']);
        Route::get('appointments', [AppointmentController::class, 'myAppointments']);
    });
});
