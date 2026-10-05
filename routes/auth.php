<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\CompleteProfileController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::middleware(['guest', 'throttle:auth'])->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::get('register/form', [RegisteredUserController::class, 'form'])
        ->name('register.form');

    Route::post('register', [RegisteredUserController::class, 'store']);

    // Office / staff applications cannot log in until an admin approves them,
    // so this confirmation page has to stay reachable without a session.
    Route::get('register/pending', [RegisteredUserController::class, 'pending'])
        ->name('register.pending');

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::get('complete-profile', [CompleteProfileController::class, 'create'])
        ->name('complete-profile');

    Route::post('complete-profile', [CompleteProfileController::class, 'store']);

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');

    // --- Email verification ------------------------------------------------
    // Deliberately behind `auth`: VerifyEmailController would otherwise hand a
    // session to whoever holds the link, and the pending / rejected / inactive
    // checks live in the login form only. A signed URL alone cannot tell an
    // office applicant apart from an approved account.
    Route::get('email/verify', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('email/verify/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
});

// NOTE: nothing enforces this yet. No route group in routes/web.php carries
// the `verified` middleware, so an unverified account walks the app exactly
// as before — by design, because the mail provider cannot currently deliver
// to arbitrary student addresses. Turning it on is one edit per group:
// change ['auth', 'role:x'] to ['auth', 'verified', 'role:x'] once mail is
// proven, and run `php artisan migrate` first so existing accounts (which
// this database backfilled) are not locked out.
