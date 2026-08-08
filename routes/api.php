<?php

use App\Http\Controllers\Api\V1\Admin\DriverRequestController as AdminDriverRequestController;
use App\Http\Controllers\Api\V1\Admin\StatsController;
use App\Http\Controllers\Api\V1\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DriverRequestController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\ReservationController;
use App\Http\Controllers\Api\V1\ReviewController;
use App\Http\Controllers\Api\V1\TripController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // --- Authentification (publique) ---
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    // --- Recherche publique de trajets (visiteur) ---
    Route::get('trips', [TripController::class, 'index']);
    Route::get('trips/{trip}', [TripController::class, 'show']);

    // --- Routes authentifiees (Sanctum) ---
    Route::middleware('auth:sanctum')->group(function () {

        Route::post('logout', [AuthController::class, 'logout']);

        Route::get('me', [ProfileController::class, 'show']);
        Route::put('me', [ProfileController::class, 'update']);

        Route::post('driver-requests', [DriverRequestController::class, 'store']);
        Route::get('driver-requests/me', [DriverRequestController::class, 'show']);

        Route::post('reports', [ReportController::class, 'store']);

        // Notifications (EF-06 : cloche / badge dans l'interface)
        Route::get('notifications', [NotificationController::class, 'index']);
        Route::patch('notifications/read-all', [NotificationController::class, 'markAllRead']);
        Route::patch('notifications/{notification}/read', [NotificationController::class, 'markRead']);

        // Trajets : creation reservee aux conducteurs valides (verifie dans le controller)
        Route::post('trips', [TripController::class, 'store']);
        Route::put('trips/{trip}', [TripController::class, 'update']);
        Route::delete('trips/{trip}', [TripController::class, 'destroy']);
        Route::patch('trips/{trip}/close', [TripController::class, 'close']);
        Route::get('me/trips', [TripController::class, 'mesTrajets']);

        // Reservations
        Route::post('trips/{trip}/reservations', [ReservationController::class, 'store']);
        Route::patch('reservations/{reservation}/accept', [ReservationController::class, 'accept']);
        Route::patch('reservations/{reservation}/refuse', [ReservationController::class, 'refuse']);
        Route::patch('reservations/{reservation}/cancel', [ReservationController::class, 'cancel']);
        Route::get('me/reservations', [ReservationController::class, 'mesReservations']);

        // Evaluations
        Route::post('reservations/{reservation}/review', [ReviewController::class, 'store']);

        // --- Espace administrateur ---
        Route::prefix('admin')->middleware('role:admin')->group(function () {
            Route::get('users', [AdminUserController::class, 'index']);
            Route::patch('users/{user}/toggle-active', [AdminUserController::class, 'toggleActive']);

            Route::get('reports', [AdminUserController::class, 'reports']);
            Route::patch('reports/{report}', [AdminUserController::class, 'updateReport']);

            Route::get('driver-requests', [AdminDriverRequestController::class, 'index']);
            Route::patch('driver-requests/{driverRequest}', [AdminDriverRequestController::class, 'update']);

            Route::get('stats', [StatsController::class, 'index']);
        });
    });
});
