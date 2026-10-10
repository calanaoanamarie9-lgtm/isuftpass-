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
    // --- Email verification ------------------------------------------------
    // Registration lands here, so `verified` is the gate that sits between
    // "Create Account" and the personal-details form: an unverified signup is
    // bounced to verification.notice and stays there until the signed link is
    // clicked. Only these two routes are gated - logout, confirm-password and
    // password.update must stay reachable, and the verification routes below
    // must never carry `verified` or the redirect would loop.
    Route::get('complete-profile', [CompleteProfileController::class, 'create'])
        ->middleware('verified')
        ->name('complete-profile');

    Route::post('complete-profile', [CompleteProfileController::class, 'store'])
        ->middleware('verified');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');

    Route::get('email/verify', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
});

// The signed verification link lives OUTSIDE `auth`: it is usually clicked
// on the phone that reads the inbox, a device with no session, and `auth`
// would drop the freshly verified user on the login page mid-flow. The
// controller signs the holder in itself — only after re-applying the same
// pending / rejected / deactivated gates the login form uses, so a signed
// link alone can never walk an unapproved office account past the approval
// gate. `signed` + throttle are what authenticate the request instead.
Route::get('email/verify/{id}/{hash}', VerifyEmailController::class)
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');

// NOTE: this is enforced. Every authenticated group in routes/web.php carries
// `verified`, and `complete-profile` carries it above, so a fresh signup can
// neither finish nor use an account until the signed link has been clicked.
// Clicking that link is what completes both: VerifyEmailController verifies
// the address, signs the holder in (gates permitting) and hands off straight
// to the profile form or the dashboard - no login page in between. An
// unverified session may still reach login, logout, these verification routes,
// confirm-password and password.update - never a dashboard.
//
// Delivery runs over Brevo (render.yaml: MAIL_MAILER=brevo), proven end to
// end before this gate went in. The migration
// 2026_10_06_000001_backfill_email_verification_before_enforcing_it marks the
// accounts that already existed, so `php artisan migrate` - Render's
// preDeployCommand - cannot lock one out; everyone registering after it must
// verify.
