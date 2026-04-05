<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\BaseController;
use App\Models\CityRequest;
use App\Models\LocationArea;
use App\Models\LocationCity;
use App\Models\LocationCountry;
use App\Models\Product;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class LocationController extends BaseController
{
    public function index(?Request $request, ?string $type = null): View|Collection|LengthAwarePaginator|null|callable|RedirectResponse
    {
        $searchValue = $request?->get('searchValue');

        $countries = LocationCountry::query()
            ->when($searchValue, fn($q) => $q->where('name', 'like', "%{$searchValue}%"))
            ->withCount('cities')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(getWebConfig(name: 'pagination_limit'));

        return view('admin-views.business-settings.location.country-list', compact('countries', 'searchValue'));
    }

    // ----- Country CRUD -----

    public function addCountry(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:location_countries,name',
            'code' => 'nullable|string|max:10',
        ]);

        LocationCountry::create([
            'name' => $request->name,
            'code' => $request->code,
            'is_active' => true,
        ]);

        ToastMagic::success(translate('country_added_successfully'));

        return back();
    }

    public function updateCountry(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:location_countries,name,' . $id,
            'code' => 'nullable|string|max:10',
        ]);

        $country = LocationCountry::findOrFail($id);
        $country->update([
            'name' => $request->name,
            'code' => $request->code,
        ]);

        ToastMagic::success(translate('country_updated_successfully'));

        return back();
    }

    public function deleteCountry(Request $request): RedirectResponse
    {
        LocationCountry::findOrFail($request->id)->delete();
        ToastMagic::success(translate('country_deleted_successfully'));

        return back();
    }

    public function updateCountryStatus(Request $request): JsonResponse
    {
        $country = LocationCountry::findOrFail($request->id);
        $country->update(['is_active' => $request->get('status', 0)]);

        return response()->json(['success' => 1, 'message' => translate('status_updated_successfully')]);
    }

    // ----- City CRUD -----

    public function cities(Request $request, int $countryId): View
    {
        $country = LocationCountry::findOrFail($countryId);
        $searchValue = $request->get('searchValue');

        $cities = LocationCity::query()
            ->where('country_id', $countryId)
            ->when($searchValue, fn($q) => $q->where('name', 'like', "%{$searchValue}%"))
            ->withCount('areas')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(getWebConfig(name: 'pagination_limit'));

        return view('admin-views.business-settings.location.city-list', compact('country', 'cities', 'searchValue'));
    }

    public function addCity(Request $request): RedirectResponse
    {
        $request->validate([
            'country_id' => 'required|exists:location_countries,id',
            'name' => 'required|string|max:100',
        ]);

        LocationCity::create([
            'country_id' => $request->country_id,
            'name' => $request->name,
            'is_active' => true,
        ]);

        ToastMagic::success(translate('city_added_successfully'));

        return back();
    }

    public function updateCity(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:100',
        ]);

        LocationCity::findOrFail($id)->update(['name' => $request->name]);
        ToastMagic::success(translate('city_updated_successfully'));

        return back();
    }

    public function deleteCity(Request $request): RedirectResponse
    {
        LocationCity::findOrFail($request->id)->delete();
        ToastMagic::success(translate('city_deleted_successfully'));

        return back();
    }

    public function updateCityStatus(Request $request): JsonResponse
    {
        $city = LocationCity::findOrFail($request->id);
        $city->update(['is_active' => $request->get('status', 0)]);

        return response()->json(['success' => 1, 'message' => translate('status_updated_successfully')]);
    }

    // ----- Area CRUD -----

    public function areas(Request $request, int $cityId): View
    {
        $city = LocationCity::with('country')->findOrFail($cityId);
        $searchValue = $request->get('searchValue');

        $areas = LocationArea::query()
            ->where('city_id', $cityId)
            ->when($searchValue, fn($q) => $q->where('name', 'like', "%{$searchValue}%"))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(getWebConfig(name: 'pagination_limit'));

        return view('admin-views.business-settings.location.area-list', compact('city', 'areas', 'searchValue'));
    }

    public function addArea(Request $request): RedirectResponse
    {
        $request->validate([
            'city_id' => 'required|exists:location_cities,id',
            'name' => 'required|string|max:100',
            'cod_available' => 'nullable|boolean',
        ]);

        LocationArea::create([
            'city_id' => $request->city_id,
            'name' => $request->name,
            'is_active' => true,
            'cod_available' => $request->boolean('cod_available', true),
        ]);

        ToastMagic::success(translate('area_added_successfully'));

        return back();
    }

    public function updateArea(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'cod_available' => 'nullable|boolean',
        ]);

        LocationArea::findOrFail($id)->update([
            'name' => $request->name,
            'cod_available' => $request->boolean('cod_available', true),
        ]);

        ToastMagic::success(translate('area_updated_successfully'));

        return back();
    }

    public function deleteArea(Request $request): RedirectResponse
    {
        LocationArea::findOrFail($request->id)->delete();
        ToastMagic::success(translate('area_deleted_successfully'));

        return back();
    }

    public function updateAreaStatus(Request $request): JsonResponse
    {
        $area = LocationArea::findOrFail($request->id);
        $area->update(['is_active' => $request->get('status', 0)]);

        return response()->json(['success' => 1, 'message' => translate('status_updated_successfully')]);
    }

    // ----- AJAX helpers for dependent dropdowns -----

    public function getCitiesByCountry(int $countryId): JsonResponse
    {
        $cities = LocationCity::where('country_id', $countryId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($cities);
    }

    public function getAreasByCity(int $cityId): JsonResponse
    {
        $areas = LocationArea::where('city_id', $cityId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'cod_available']);

        return response()->json($areas);
    }

    public function getCountries(): JsonResponse
    {
        $countries = LocationCountry::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return response()->json($countries);
    }

    // ----- City Request Management -----

    public function cityRequests(Request $request): View
    {
        $searchValue = $request->get('searchValue');
        $status = $request->get('status');

        $cityRequests = CityRequest::query()
            ->with(['seller', 'country', 'approvedCity'])
            ->when($searchValue, fn($q) => $q->where('city_name', 'like', "%{$searchValue}%"))
            ->when($status, fn($q) => $q->where('status', $status))
            ->latest()
            ->paginate(getWebConfig(name: 'pagination_limit'));

        return view('admin-views.business-settings.location.city-requests', compact('cityRequests', 'searchValue', 'status'));
    }

    public function approveCityRequest(Request $request, int $id): RedirectResponse
    {
        $cityRequest = CityRequest::with('country')->findOrFail($id);

        if ($cityRequest->status !== 'pending') {
            ToastMagic::error(translate('this_request_has_already_been_processed'));

            return back();
        }

        $city = LocationCity::create([
            'country_id' => $cityRequest->country_id,
            'name' => $cityRequest->city_name,
            'is_active' => true,
        ]);

        $cityRequest->update([
            'status' => 'approved',
            'approved_city_id' => $city->id,
        ]);

        // Auto-attach the approved city to any products that were linked to this city request
        Product::where('pending_city_request_id', $cityRequest->id)
            ->update([
                'location_city_id' => $city->id,
                'pending_city_request_id' => null,
            ]);

        ToastMagic::success(translate('city_request_approved_and_city_created'));

        return back();
    }

    public function rejectCityRequest(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'admin_note' => 'nullable|string|max:500',
        ]);

        $cityRequest = CityRequest::findOrFail($id);

        if ($cityRequest->status !== 'pending') {
            ToastMagic::error(translate('this_request_has_already_been_processed'));

            return back();
        }

        $cityRequest->update([
            'status' => 'rejected',
            'admin_note' => $request->admin_note,
        ]);

        ToastMagic::success(translate('city_request_rejected'));

        return back();
    }
}
