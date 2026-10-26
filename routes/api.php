<?php

use App\Http\Controllers\Api\FeedbackController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Loaded by bootstrap/app.php. The feedback endpoint is called by the
| signed-in page itself, so the group carries the session stack (see
| Middleware::api() in bootstrap/app.php) and authenticates with the
| normal web guard instead of a token guard.
|
*/

Route::middleware('auth')->group(function () {
    Route::post('/feedback/store', [FeedbackController::class, 'store'])
        ->name('api.feedback.store');
});
