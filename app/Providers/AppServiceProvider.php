<?php

namespace App\Providers;

use App\Models\Settings;
use Illuminate\Support\ServiceProvider;
use Mollie\Api\MollieApiClient;
use Spatie\Dropbox\Client;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton('mollie', function ($app) {
            if (env('APP_DEBUG')) {
                $value = Settings::byKey('mollie_sandbox');
            } else {
                $value = Settings::byKey('mollie_production');
            }
            $mollie = new MollieApiClient();
            $mollie->setApiKey($value);
            return $mollie;
        });

        $this->app->singleton('dropbox', function ($app) {
            return new Client(env("DROPBOX_KEY"));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

    }
}
