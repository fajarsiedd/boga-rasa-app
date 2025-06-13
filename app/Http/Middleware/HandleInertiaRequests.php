<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        $abilities = [];

        if ($user) {
            $abilities = [
                // Users
                'view-users' => $user->can('view-users'),
                'create-user' => $user->can('create-users'),
                'edit-user' => $user->can('edit-users'),
                'delete-user' => $user->can('delete-users'),

                // Customer types
                'view-customer-types' => $user->can('view-customer-types'),
                'create-customer-type' => $user->can('create-customer-type'),
                'edit-customer-type' => $user->can('edit-customer-type'),
                'delete-customer-type' => $user->can('delete-customer-type'),

                // Customers
                'view-customers' => $user->can('view-customers'),
                'create-customer' => $user->can('create-customer'),
                'edit-customer' => $user->can('edit-customer'),
                'delete-customer' => $user->can('delete-customer'),

                // Materials
                'view-materials' => $user->can('view-materials'),
                'create-material' => $user->can('create-material'),
                'edit-material' => $user->can('edit-material'),
                'delete-material' => $user->can('delete-material'),

                // Suppliers
                'view-suppliers' => $user->can('view-suppliers'),
                'create-supplier' => $user->can('create-supplier'),
                'edit-supplier' => $user->can('edit-supplier'),
                'delete-supplier' => $user->can('delete-supplier'),

                // Products
                'view-products' => $user->can('view-products'),
                'create-product' => $user->can('create-product'),
                'edit-product' => $user->can('edit-product'),
                'delete-product' => $user->can('delete-product'),

                // Purchases
                'view-purchases' => $user->can('view-purchases'),
                'create-purchase' => $user->can('create-purchase'),
                'edit-purchase' => $user->can('edit-purchase'),
                'delete-purchase' => $user->can('delete-purchase'),

                // Sales
                'view-sales' => $user->can('view-sales'),
                'create-sale' => $user->can('create-sale'),
                'edit-sale' => $user->can('edit-sale'),
                'delete-sale' => $user->can('delete-sale'),

                // Orders
                'view-orders' => $user->can('view-orders'),
                'create-order' => $user->can('create-order'),
                'edit-order' => $user->can('edit-order'),
                'delete-order' => $user->can('delete-order'),

                // Receivables
                'view-receivables' => $user->can('view-receivables'),                
                'edit-receivable' => $user->can('edit-receivable'),
                'delete-receivable' => $user->can('delete-receivable'),
            ];
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'role' => $user->role,
                    'can' => $abilities
                ] : null,
            ],            
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ];
    }
}
