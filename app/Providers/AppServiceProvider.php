<?php

namespace App\Providers;

use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;

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
        //
        Config::set('fortify.home', '/dashboard');
        Config::set('auth.paths.home', '/dashboard');

        if (env('APP_ENV') == 'production') {
            $url->forceScheme('https');
        }
    }
}
