<?php

namespace App\Providers;

use App\Enums\Permission;
use App\Models\Document;
use App\Models\User;
use App\Transport\BrevoTransport;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Generated links (verification emails, notifications) must use the
        // configured app.url instead of whatever host the browser happened
        // to use — a "127.0.0.1" link opened on a phone points at the phone
        // itself, not this server.
        //
        // The exception is a request served on the local dev host: pinning
        // the live URL there would make every in-app link and form action
        // jump to the Render site while testing locally, so the dev host
        // keeps its own root. Console runs (queue, scheduler) still pin to
        // app.url and production already serves app.url, so neither changes
        // — the only cost is that emails rendered during a localhost request
        // carry the dev host.
        if (app()->runningInConsole() || ! in_array(request()->getHost(), ['localhost', '127.0.0.1', '[::1]', '::1'], true)) {
            URL::forceRootUrl(config('app.url'));
        }

        RateLimiter::for('auth', fn (Request $request) => Limit::perMinute(10)->by($request->ip()));

        $this->registerBrevoTransport();
        $this->registerAuthorizationGates();
    }

    /**
     * Role-based gates for the public account types.
     *
     * The staff side of the app is routed by role middleware; these gates
     * cover what a student / alumni / parent / guest may request or book, and
     * are the backstop the controllers call so the rules hold even when the
     * UI is bypassed. Roles and permissions are defined in App\Enums\Role and
     * App\Enums\Permission.
     */
    private function registerAuthorizationGates(): void
    {
        // The System Admin keeps master reach over the user-facing requests.
        Gate::before(function (?User $user, string $ability) {
            if ($user?->isAdmin() && in_array($ability, ['request-document', 'book-appointment', 'request-visitor-pass'], true)) {
                return true;
            }

            return null;
        });

        Gate::define(
            'request-document',
            fn (?User $user, Document $document): bool => $user !== null
                && $user->canRequestDocuments()
                && $document->allows($user->classification())
        );

        Gate::define(
            'book-appointment',
            fn (?User $user, string $office): bool => $user !== null && $user->canBookOffice($office)
        );

        Gate::define(
            'request-visitor-pass',
            fn (?User $user): bool => $user !== null && $user->hasPermission(Permission::RequestVisitorPass)
        );
    }

    /**
     * Teach the mail manager a driver Laravel does not ship.
     *
     * Registering it here rather than inside config/mail.php keeps the
     * config file declaring *what* is used and this class declaring *how*,
     * and it means the key is read from config() - the only thing that
     * still returns a value once the config is cached on Render.
     */
    private function registerBrevoTransport(): void
    {
        Mail::extend('brevo', fn (array $config) => new BrevoTransport(
            apiKey: (string) ($config['key'] ?? config('services.brevo.key')),
        ));
    }
}
