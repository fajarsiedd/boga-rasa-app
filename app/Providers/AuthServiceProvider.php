<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Authorization
        // Users
        Gate::define('view-users', function (User $user) {
            return in_array($user->role, ['owner']);
        });

        Gate::define('create-user', function (User $user) {
            return in_array($user->role, ['owner']);
        });

        Gate::define('edit-user', function (User $user) {
            return in_array($user->role, ['owner']);
        });

        Gate::define('delete-user', function (User $user) {
            return in_array($user->role, ['owner']);
        });

        // Customer type
        Gate::define('view-customer-types', function (User $user) {
            return in_array($user->role, ['owner', 'admin', 'finance']);
        });

        Gate::define('create-customer-type', function (User $user) {
            return in_array($user->role, ['owner', 'admin']);
        });

        Gate::define('edit-customer-type', function (User $user) {
            return in_array($user->role, ['owner', 'admin']);
        });

        Gate::define('delete-customer-type', function (User $user) {
            return in_array($user->role, ['owner', 'admin']);
        });

        // Customer
        Gate::define('view-customers', function (User $user) {
            return in_array($user->role, ['owner', 'admin', 'finance']);
        });

        Gate::define('create-customer', function (User $user) {
            return in_array($user->role, ['owner', 'admin']);
        });

        Gate::define('edit-customer', function (User $user) {
            return in_array($user->role, ['owner', 'admin']);
        });

        Gate::define('delete-customer', function (User $user) {
            return in_array($user->role, ['owner', 'admin']);
        });

        // Material
        Gate::define('view-materials', function (User $user) {
            return in_array($user->role, ['owner', 'admin', 'finance']);
        });

        Gate::define('create-material', function (User $user) {
            return in_array($user->role, ['owner', 'admin']);
        });

        Gate::define('edit-material', function (User $user) {
            return in_array($user->role, ['owner', 'admin']);
        });

        Gate::define('delete-material', function (User $user) {
            return in_array($user->role, ['owner', 'admin']);
        });

        // Supplier
        Gate::define('view-suppliers', function (User $user) {
            return in_array($user->role, ['owner', 'admin', 'finance']);
        });

        Gate::define('create-supplier', function (User $user) {
            return in_array($user->role, ['owner', 'admin']);
        });

        Gate::define('edit-supplier', function (User $user) {
            return in_array($user->role, ['owner', 'admin']);
        });

        Gate::define('delete-supplier', function (User $user) {
            return in_array($user->role, ['owner', 'admin']);
        });

        // Product
        Gate::define('view-products', function (User $user) {
            return in_array($user->role, ['owner', 'admin', 'finance']);
        });

        Gate::define('create-product', function (User $user) {
            return in_array($user->role, ['owner', 'finance']);
        });

        Gate::define('edit-product', function (User $user) {
            return in_array($user->role, ['owner', 'finance']);
        });

        Gate::define('delete-product', function (User $user) {
            return in_array($user->role, ['owner', 'finance']);
        });

        // Purchase
        Gate::define('view-purchases', function (User $user) {
            return in_array($user->role, ['owner', 'admin', 'finance']);
        });

        Gate::define('create-purchase', function (User $user) {
            return in_array($user->role, ['owner', 'admin']);
        });

        Gate::define('edit-purchase', function (User $user) {
            return in_array($user->role, ['owner', 'admin']);
        });

        Gate::define('delete-purchase', function (User $user) {
            return in_array($user->role, ['owner', 'admin']);
        });

        // Sales
        Gate::define('view-sales', function (User $user) {
            return in_array($user->role, ['owner', 'admin', 'finance']);
        });

        Gate::define('create-sale', function (User $user) {
            return in_array($user->role, ['owner', 'admin']);
        });

        Gate::define('edit-sale', function (User $user) {
            return in_array($user->role, ['owner', 'admin']);
        });

        Gate::define('delete-sale', function (User $user) {
            return in_array($user->role, ['owner', 'admin']);
        });

        // Orders
        Gate::define('view-orders', function (User $user) {
            return in_array($user->role, ['owner', 'admin', 'finance']);
        });

        Gate::define('create-order', function (User $user) {
            return in_array($user->role, ['owner', 'admin']);
        });

        Gate::define('edit-order', function (User $user) {
            return in_array($user->role, ['owner', 'admin']);
        });

        Gate::define('delete-order', function (User $user) {
            return in_array($user->role, ['owner', 'admin']);
        });

        // Receivable
        Gate::define('view-receivables', function (User $user) {
            return in_array($user->role, ['owner', 'admin', 'finance']);
        });

        Gate::define('edit-receivable', function (User $user) {
            return in_array($user->role, ['owner', 'finance']);
        });

        Gate::define('delete-receivable', function (User $user) {
            return in_array($user->role, ['owner', 'finance']);
        });
    }
}
