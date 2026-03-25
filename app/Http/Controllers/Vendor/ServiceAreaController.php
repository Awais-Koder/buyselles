<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\BaseController;
use App\Models\LocationArea;
use App\Models\LocationCountry;
use App\Models\SellerServiceArea;
use App\Models\VendorShippingRate;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ServiceAreaController extends BaseController
{
    public function index(?Request $request, ?string $type = null): View|Collection|LengthAwarePaginator|null|callable|RedirectResponse
    {
        $sellerId = auth('seller')->id();

        $countries = LocationCountry::query()
            ->where('is_active', true)
            ->with(['cities' => function ($q) {
                $q->where('is_active', true)->with(['areas' => function ($q2) {
                    $q2->where('is_active', true)->orderBy('sort_order')->orderBy('name');
                }]);
            }])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $selectedAreaIds = SellerServiceArea::query()
            ->where('seller_id', $sellerId)
            ->pluck('area_id')
            ->toArray();

        $shippingRates = VendorShippingRate::query()
            ->where('seller_id', $sellerId)
            ->get()
            ->keyBy('area_id');

        return view('vendor-views.shop.service-areas', compact('countries', 'selectedAreaIds', 'shippingRates'));
    }

    public function update(Request $request): RedirectResponse
    {
        $sellerId = auth('seller')->id();
        $areaIds = $request->input('area_ids', []);
        $shippingCosts = $request->input('shipping_cost', []);
        $estimatedDays = $request->input('estimated_days', []);

        // Validate that submitted areas actually exist and are active
        $validAreaIds = LocationArea::query()
            ->where('is_active', true)
            ->whereIn('id', $areaIds)
            ->pluck('id')
            ->toArray();

        // Sync service areas — remove old, add new
        SellerServiceArea::query()->where('seller_id', $sellerId)->delete();

        foreach ($validAreaIds as $areaId) {
            SellerServiceArea::query()->create([
                'seller_id' => $sellerId,
                'area_id' => $areaId,
            ]);
        }

        // Sync shipping rates — remove old, add new
        VendorShippingRate::query()->where('seller_id', $sellerId)->delete();

        foreach ($validAreaIds as $areaId) {
            $cost = isset($shippingCosts[$areaId]) ? (float) $shippingCosts[$areaId] : 0;
            $days = isset($estimatedDays[$areaId]) && $estimatedDays[$areaId] !== '' ? (int) $estimatedDays[$areaId] : null;

            VendorShippingRate::query()->create([
                'seller_id' => $sellerId,
                'area_id' => $areaId,
                'shipping_cost' => $cost,
                'estimated_days' => $days,
            ]);
        }

        ToastMagic::success(translate('Service_areas_and_shipping_rates_updated_successfully'));

        return back();
    }

    /**
     * Get cities by country (AJAX).
     */
    public function getCitiesByCountry(string|int $countryId): JsonResponse
    {
        $cities = LocationCountry::findOrFail($countryId)
            ->cities()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($cities);
    }

    /**
     * Get areas by city (AJAX).
     */
    public function getAreasByCity(string|int $cityId): JsonResponse
    {
        $areas = \App\Models\LocationCity::findOrFail($cityId)
            ->areas()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'cod_available']);

        return response()->json($areas);
    }
}
