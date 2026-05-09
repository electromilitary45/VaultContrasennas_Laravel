<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    // Registro
    Route::get('register', [AuthController::class, 'showRegister'])
        ->name('register');
    Route::post('register', [AuthController::class, 'register']);

    // Login
    Route::get('login', [AuthController::class, 'showLogin'])
        ->name('login');
    Route::post('login', [AuthController::class, 'login']);

    // Verificación 2FA durante login
    Route::get('two-factor/login', [AuthController::class, 'showTwoFactorLogin'])
        ->name('two-factor.login');
    Route::post('two-factor/login', [AuthController::class, 'verifyTwoFactorLogin'])
        ->name('two-factor.verify');

    // Recuperación de contraseña
    Route::get('forgot-password', [AuthController::class, 'showForgotPassword'])
        ->name('password.request');
    Route::post('forgot-password', [AuthController::class, 'sendPasswordResetLink'])
        ->name('password.email');

    // Reset de contraseña
    Route::get('reset-password/{token}', [AuthController::class, 'showResetPassword'])
        ->name('password.reset');
    Route::post('reset-password', [AuthController::class, 'resetPassword'])
        ->name('password.store');
});

Route::middleware('auth')->group(function () {
    // Verificación de email
    Route::get('verify-email', [AuthController::class, 'showEmailVerification'])
        ->name('verification.notice');
    Route::get('verify-email/{id}/{hash}', [AuthController::class, 'verifyEmail'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('email/verification-notification', [AuthController::class, 'resendVerificationEmail'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    // Confirmación de contraseña
    Route::get('confirm-password', [AuthController::class, 'showConfirmPassword'])
        ->name('password.confirm');
    Route::post('confirm-password', [AuthController::class, 'confirmPassword']);

    // Actualizar contraseña
    Route::put('password', [AuthController::class, 'updatePassword'])
        ->name('password.update');

    // Cambio obligatorio (ej. primer login con contraseña temporal)
    Route::get('password/change-required', [AuthController::class, 'showChangeRequired'])
        ->name('password.change-required');
    Route::post('password/change-required', [AuthController::class, 'storeChangeRequired'])
        ->name('password.change-required.store');

    // Logout
    Route::post('logout', [AuthController::class, 'logout'])
        ->name('logout');
});
