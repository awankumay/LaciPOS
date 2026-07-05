<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Models\StoreProfile;

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
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'role' => $request->user()->role ?? null,
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
                'print_success' => fn () => $request->session()->get('print_success'),
            ],
            'onboardingCompleted' => fn () => StoreProfile::isOnboardingCompleted(),
            'storeSettings' => function () {
                $profile = StoreProfile::getProfile();
                return $profile ? [
                    'tax_enabled' => $profile->tax_enabled,
                    'tax_type' => $profile->tax_type,
                    'tax_value' => $profile->tax_value,
                    'service_charge_enabled' => $profile->service_charge_enabled,
                    'service_charge_type' => $profile->service_charge_type,
                    'service_charge_value' => $profile->service_charge_value,
                    'timezone' => $profile->timezone ?? 'Asia/Jakarta',
                    'enable_customer_name' => $profile->enable_customer_name,
                    'enable_table_number' => $profile->enable_table_number,
                    'enable_order_notes' => $profile->enable_order_notes,
                ] : null;
            },
        ];
    }
}
