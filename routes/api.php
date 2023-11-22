<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('v1')->group(function() {
    Route::post('register', [AuthController::class, 'register'])->name('register');
    Route::post('login', [AuthController::class, 'login'])->name('login');
    Route::post('forgot-password', [AuthController::class, 'sendResetPasswordLink'])->middleware('throttle:5,1')->name('password.email');
    Route::post('verification-notification', [AuthController::class, 'verificationNotification'])->middleware('throttle:6,1')->name('verification.send');
    Route::get('verify-email/{ulid}/{hash}', [AuthController::class, 'emailVirify'])->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('reset-password', [AuthController::class, 'resetPassword'])->name('password.store');

    Route::middleware(['auth:sanctum', 'verified'])->group(function() {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('user', [AuthController::class, 'user']);
    });
});
