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
        Gate::define('owner-only', function ($user) {
            $role = strtolower($user->role ?? '');
            return $role === 'admin' || $role === 'owner' || $role === 'superadmin';
        });
    }
}
