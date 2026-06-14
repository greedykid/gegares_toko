<?php

namespace App\Providers;

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
        // Force HTTPS only if using a tunnel URL or explicitly set in APP_URL, 
        // but avoid forcing it if accessing via local development server directly.
        if (!app()->isLocal() || (str_contains(config('app.url'), 'https://') && !request()->is('localhost*') && !request()->is('127.0.0.1*'))) {
            URL::forceScheme('https');
        }

        // if (env('APP_ENV') === 'local') {
        //     URL::forceScheme('https');
        //}
    }
}
