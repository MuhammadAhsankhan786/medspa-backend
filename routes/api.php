<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ConsentFormController;
use App\Http\Controllers\TreatmentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PackageController;

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
    | Admin routes
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        // Appointments
        Route::get('appointments', [AppointmentController::class, 'index']);
        Route::get('appointments/{appointment}', [AppointmentController::class, 'show']);
        Route::patch('appointments/{appointment}/status', [AppointmentController::class, 'updateStatus']);

        // Consent Forms
        Route::apiResource('consent-forms', ConsentFormController::class);

        // Treatments
        Route::apiResource('treatments', TreatmentController::class);

        // Payments
        Route::apiResource('payments', PaymentController::class);

        // Packages
        Route::apiResource('packages', PackageController::class);
        Route::post('packages/assign', [PackageController::class, 'assignToClient']);
    });

    /*
    |--------------------------------------------------------------------------
    | Staff (provider + reception) routes
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:provider,reception')->prefix('staff')->group(function () {
        // Appointments
        Route::get('appointments', [AppointmentController::class, 'index']);
        Route::get('appointments/{appointment}', [AppointmentController::class, 'show']);
        Route::patch('appointments/{appointment}/status', [AppointmentController::class, 'updateStatus']);

        // Consent Forms
        Route::apiResource('consent-forms', ConsentFormController::class);

        // Treatments
        Route::apiResource('treatments', TreatmentController::class);

        // Payments (read-only for staff)
        Route::apiResource('payments', PaymentController::class)->only(['index','show']);

        // Packages (read-only for staff)
        Route::apiResource('packages', PackageController::class)->only(['index','show']);
    });

    /*
    |--------------------------------------------------------------------------
    | Client routes
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:client')->prefix('client')->group(function () {
        // Appointments
        Route::get('appointments', [AppointmentController::class, 'myAppointments']);
        Route::get('appointments/{appointment}', [AppointmentController::class, 'show']);
        Route::post('appointments', [AppointmentController::class, 'store']);
        Route::delete('appointments/{appointment}', [AppointmentController::class, 'destroy']);

        // Consent Forms (client can only manage own)
        Route::apiResource('consent-forms', ConsentFormController::class)->only([
            'index', 'store', 'show', 'update', 'destroy'
        ]);

        // Treatments (client can only view & create)
        Route::apiResource('treatments', TreatmentController::class)->only([
            'index', 'store', 'show'
        ]);

        // Payments
        Route::get('payments', [PaymentController::class, 'myPayments']);
        Route::post('payments', [PaymentController::class, 'pay']);

        // Packages
        Route::get('packages', [PackageController::class, 'myPackages']);
    });

    /*
    |--------------------------------------------------------------------------
    | Notifications (all roles)
    |--------------------------------------------------------------------------
    */
    Route::get('notifications', [NotificationController::class, 'index']);
    Route::get('notifications/unread', [NotificationController::class, 'unread']);
    Route::post('notifications/{id}/read', [NotificationController::class, 'markAsRead']);
});
