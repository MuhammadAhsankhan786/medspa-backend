<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

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
    |--------------------------------------------------------------------------
    | Role-based Dashboards
    |--------------------------------------------------------------------------
    */

    // Admin only
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/dashboard', fn () => response()->json(['message' => 'Welcome Admin']));
    });

    // Provider only
    Route::middleware('role:provider')->prefix('provider')->group(function () {
        Route::get('/dashboard', fn () => response()->json(['message' => 'Welcome Provider']));
    });

    // Reception only
    Route::middleware('role:reception')->prefix('reception')->group(function () {
        Route::get('/dashboard', fn () => response()->json(['message' => 'Welcome Reception']));
    });

    // Client only
    Route::middleware('role:client')->prefix('client')->group(function () {
        Route::get('/dashboard', fn () => response()->json(['message' => 'Welcome Client']));
    });

    // Staff (Provider + Reception allowed)
    Route::middleware('role:provider,reception')->prefix('staff')->group(function () {
        Route::get('/dashboard', fn () => response()->json(['message' => 'Welcome Staff']));
    });
});
