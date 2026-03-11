<?php

namespace App\Providers;

use App\Models\Settings;
use Illuminate\Support\Facades\Cache;
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
            $dropboxKey = Cache::get('dropbox_token');
            if (empty($dropboxKey)) {
                throw new \Exception("Dropbox key not found");
            }
            return new Client($dropboxKey);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

    }
}
