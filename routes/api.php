<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::get('me', [AuthController::class, 'me']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);

    // 🔹 Role-based routes
    Route::middleware('role:admin')->get('/admin/dashboard', function () {
        return response()->json(['message' => 'Welcome Admin']);
    });

    Route::middleware('role:provider')->get('/provider/dashboard', function () {
        return response()->json(['message' => 'Welcome Provider']);
    });

    Route::middleware('role:reception')->get('/reception/dashboard', function () {
        return response()->json(['message' => 'Welcome Reception']);
    });

    Route::middleware('role:client')->get('/client/dashboard', function () {
        return response()->json(['message' => 'Welcome Client']);
    });
});
