<?php

namespace App\Http\Controllers\Admin\Vendor;

use App\Enums\GlobalConstant;
use App\Http\Controllers\BaseController;
use App\Models\Seller;
use App\Models\VendorPermission;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VendorPermissionController extends BaseController
{
    /**
     * List all vendors with their current permission status.
     */
    public function index(?Request $request = null, ?string $type = null): View
    {
        $vendorModulePermission = GlobalConstant::VENDOR_MODULE_PERMISSION;

        $vendors = Seller::with('shop', 'vendorPermission')
            ->when($request?->filled('search'), function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('f_name', 'like', '%'.$request->search.'%')
                        ->orWhere('l_name', 'like', '%'.$request->search.'%')
                        ->orWhere('email', 'like', '%'.$request->search.'%');
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('admin-views.vendor-permission.index', compact('vendors', 'vendorModulePermission'));
    }

    /**
     * Show the permission edit form for a single vendor.
     */
    public function edit(int $sellerId): View
    {
        $vendorModulePermission = GlobalConstant::VENDOR_MODULE_PERMISSION;
        $seller = Seller::with('shop', 'vendorPermission')->findOrFail($sellerId);

        return view('admin-views.vendor-permission.edit', compact('seller', 'vendorModulePermission'));
    }

    /**
     * Save (upsert) permissions for a vendor.
     */
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'seller_id' => ['required', 'exists:sellers,id'],
        ]);

        $modules = $request->input('modules', []);

        VendorPermission::updateOrCreate(
            ['seller_id' => $request->seller_id],
            ['module_access' => count($modules) > 0 ? $modules : null],
        );

        ToastMagic::success(translate('vendor_permissions_updated_successfully'));

        return redirect()->route('admin.vendor-permission.edit', $request->seller_id);
    }
}
