<?php

namespace App\Providers;

use App\Models\Settings;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Course;
use App\Policies\CoursePolicy;
use Mollie\Api\MollieApiClient;

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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('course.manage', [CoursePolicy::class, 'manage']);
    }
}
