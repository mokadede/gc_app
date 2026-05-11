<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Owner only (admin role)
        Gate::define('owner-only', function (User $user) {
            return strtolower($user->role) === 'owner';
        });
    }
}
