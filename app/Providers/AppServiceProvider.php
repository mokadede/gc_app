<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

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
        // Authorization Gates for admin/kasir
        Gate::define('manage-services', function ($user) {
            return in_array($user->role, ['admin', 'kasir']);
        });

        Gate::define('view-reports', function ($user) {
            return in_array($user->role, ['admin', 'kasir']);
        });
    }
}
