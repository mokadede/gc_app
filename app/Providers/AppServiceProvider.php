<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

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
            $role = strtolower($user->role);
            return $role === 'owner' || $role === 'admin';
        });
    }
}
