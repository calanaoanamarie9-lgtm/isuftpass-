<?php

namespace App\Providers;

use App\Transport\BrevoTransport;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
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
        URL::forceRootUrl(config('app.url'));

        RateLimiter::for('auth', fn (Request $request) => Limit::perMinute(10)->by($request->ip()));

        $this->registerBrevoTransport();
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
