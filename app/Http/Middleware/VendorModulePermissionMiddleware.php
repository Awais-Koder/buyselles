<?php

namespace App\Http\Middleware;

use App\Models\VendorPermission;
use Closure;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Http\Request;

class VendorModulePermissionMiddleware
{
    /**
     * Module key → route prefix mapping.
     * Add entries here as the vendor panel grows.
     *
     * @var array<string, string[]>
     */
    private const ROUTE_MAP = [
        'vendor_dashboard' => ['vendor.dashboard'],
        'vendor_products' => ['vendor.products'],
        'vendor_orders' => ['vendor.orders'],
        'vendor_refund' => ['vendor.refund'],
        'vendor_customer' => ['vendor.customer'],
        'vendor_reviews' => ['vendor.reviews'],
        'vendor_coupon' => ['vendor.coupon'],
        'vendor_clearance_sale' => ['vendor.clearance-sale'],
        'vendor_messages' => ['vendor.messages'],
        'vendor_delivery_man' => ['vendor.delivery-man'],
        'vendor_wallet' => ['vendor.wallet', 'vendor.withdraw'],
        'vendor_reports' => ['vendor.report', 'vendor.transaction'],
        'vendor_shop_settings' => ['vendor.shop'],
        'vendor_business_settings' => ['vendor.business-settings'],
    ];

    /**
     * Handle an incoming vendor panel request.
     */
    public function handle(Request $request, Closure $next, string $module): mixed
    {
        if (! auth('seller')->check()) {
            return $next($request);
        }

        $sellerId = auth('seller')->id();

        $permission = VendorPermission::where('seller_id', $sellerId)->first();

        // No record or null module_access = full access (unrestricted).
        if (! $permission || $permission->module_access === null || count($permission->module_access) === 0) {
            return $next($request);
        }

        if (in_array($module, $permission->module_access)) {
            return $next($request);
        }

        ToastMagic::error(translate('access_Denied').'!');

        return redirect()->route('vendor.dashboard.index');
    }
}
