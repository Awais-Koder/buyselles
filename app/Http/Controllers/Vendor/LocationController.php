<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\BaseController;
use App\Models\CityRequest;
use App\Models\LocationArea;
use App\Models\LocationCity;
use App\Models\LocationCountry;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class LocationController extends BaseController
{
    public function index(?Request $request = null, ?string $type = null): View|Collection|LengthAwarePaginator|null|callable|RedirectResponse|JsonResponse
    {
        return $this->countries($request ?? request());
    }

    // =====================================================================
    //  Browse global locations (read-only for countries & cities)
    // =====================================================================

    public function countries(Request $request): View
    {
        $searchValue = $request->get('searchValue');

        $countries = LocationCountry::query()
            ->where('is_active', true)
            ->when($searchValue, fn ($q) => $q->where('name', 'like', "%{$searchValue}%"))
            ->withCount('cities')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(getWebConfig(name: 'pagination_limit'));

        $cityRequests = CityRequest::query()
            ->where('seller_id', auth('seller')->id())
            ->latest()
            ->limit(20)
            ->get();

        return view('vendor-views.shop.location.country-list', compact('countries', 'searchValue', 'cityRequests'));
    }

    public function cities(Request $request, int $countryId): View
    {
        $country = LocationCountry::where('id', $countryId)
            ->where('is_active', true)
            ->firstOrFail();
        $searchValue = $request->get('searchValue');

        $cities = LocationCity::query()
            ->where('country_id', $countryId)
            ->where('is_active', true)
            ->when($searchValue, fn ($q) => $q->where('name', 'like', "%{$searchValue}%"))
            ->withCount('areas')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(getWebConfig(name: 'pagination_limit'));

        $cityRequests = CityRequest::query()
            ->where('seller_id', auth('seller')->id())
            ->where('country_id', $countryId)
            ->latest()
            ->limit(20)
            ->get();

        return view('vendor-views.shop.location.city-list', compact('country', 'cities', 'searchValue', 'cityRequests'));
    }

    public function areas(Request $request, int $cityId): View
    {
        $city = LocationCity::with('country')->findOrFail($cityId);

        $searchValue = $request->get('searchValue');

        $areas = LocationArea::query()
            ->where('city_id', $cityId)
            ->when($searchValue, fn ($q) => $q->where('name', 'like', "%{$searchValue}%"))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(getWebConfig(name: 'pagination_limit'));

        return view('vendor-views.shop.location.area-list', compact('city', 'areas', 'searchValue'));
    }

    // =====================================================================
    //  City Request — vendor requests admin to add a city
    // =====================================================================

    public function requestCity(Request $request): JsonResponse
    {
        $request->validate([
            'country_id' => 'required|exists:location_countries,id',
            'city_name' => ['required', 'string', 'max:191', function ($attribute, $value, $fail) use ($request) {
                // Check if city already exists
                $exists = LocationCity::where('country_id', $request->integer('country_id'))
                    ->whereRaw('LOWER(name) = ?', [mb_strtolower($value)])
                    ->exists();
                if ($exists) {
                    $fail(translate('this_city_already_exists'));
                }

                // Check if a pending request already exists
                $pendingExists = CityRequest::where('seller_id', auth('seller')->id())
                    ->where('country_id', $request->integer('country_id'))
                    ->whereRaw('LOWER(city_name) = ?', [mb_strtolower($value)])
                    ->where('status', 'pending')
                    ->exists();
                if ($pendingExists) {
                    $fail(translate('a_request_for_this_city_is_already_pending'));
                }
            }],
        ]);

        $cityRequest = CityRequest::create([
            'seller_id' => auth('seller')->id(),
            'country_id' => $request->integer('country_id'),
            'city_name' => $request->input('city_name'),
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => translate('city_request_submitted_successfully'),
            'city_request' => $cityRequest->only(['id', 'city_name', 'status']),
        ]);
    }

    // =====================================================================
    //  Area CRUD — vendors can still manage areas under global cities
    // =====================================================================

    public function addArea(Request $request): RedirectResponse
    {
        $request->validate([
            'city_id' => 'required|exists:location_cities,id',
            'name' => ['required', 'string', 'max:100', function ($attribute, $value, $fail) use ($request) {
                $exists = LocationArea::where('city_id', $request->city_id)
                    ->whereRaw('LOWER(name) = ?', [mb_strtolower($value)])
                    ->exists();
                if ($exists) {
                    $fail(translate('this_area_already_exists'));
                }
            }],
            'cod_available' => 'nullable|boolean',
        ]);

        LocationArea::create([
            'city_id' => $request->city_id,
            'name' => $request->name,
            'is_active' => true,
            'cod_available' => $request->boolean('cod_available', false),
        ]);

        ToastMagic::success(translate('area_added_successfully'));

        return back();
    }

    public function updateArea(Request $request, int $id): RedirectResponse
    {
        $area = LocationArea::findOrFail($id);

        $request->validate([
            'name' => ['required', 'string', 'max:100', function ($attribute, $value, $fail) use ($area, $id) {
                $exists = LocationArea::where('city_id', $area->city_id)
                    ->whereRaw('LOWER(name) = ?', [mb_strtolower($value)])
                    ->where('id', '!=', $id)
                    ->exists();
                if ($exists) {
                    $fail(translate('this_area_already_exists'));
                }
            }],
            'cod_available' => 'nullable|boolean',
        ]);

        $area->update([
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

    // =====================================================================
    //  AJAX helpers — product form dropdowns (global, no seller_id filter)
    // =====================================================================

    public function getProductFormCountries(): JsonResponse
    {
        $countries = LocationCountry::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($countries);
    }

    public function getProductFormCities(string|int $countryId): JsonResponse
    {
        $cities = LocationCity::query()
            ->where('country_id', $countryId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($cities);
    }

    public function getProductFormAreas(string|int $cityId): JsonResponse
    {
        $areas = LocationArea::query()
            ->where('city_id', $cityId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($areas);
    }

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

    // =====================================================================
    //  Quick-add from product form — city request + area direct add
    // =====================================================================

    public function quickAddCityRequest(Request $request): JsonResponse
    {
        $request->validate([
            'city_name' => ['required', 'string', 'max:191', function ($attribute, $value, $fail) use ($request) {
                $exists = LocationCity::where('country_id', $request->integer('country_id'))
                    ->whereRaw('LOWER(name) = ?', [mb_strtolower($value)])
                    ->exists();
                if ($exists) {
                    $fail(translate('this_city_already_exists'));
                }

                $pendingExists = CityRequest::where('seller_id', auth('seller')->id())
                    ->where('country_id', $request->integer('country_id'))
                    ->whereRaw('LOWER(city_name) = ?', [mb_strtolower($value)])
                    ->where('status', 'pending')
                    ->exists();
                if ($pendingExists) {
                    $fail(translate('a_request_for_this_city_is_already_pending'));
                }
            }],
            'country_id' => ['required', 'integer', 'exists:location_countries,id'],
        ]);

        $cityRequest = CityRequest::create([
            'seller_id' => auth('seller')->id(),
            'country_id' => $request->integer('country_id'),
            'city_name' => $request->input('city_name'),
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => translate('city_request_submitted_for_admin_approval'),
            'city_request' => $cityRequest->only(['id', 'city_name', 'status']),
        ]);
    }

    public function quickAddArea(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:191', function ($attribute, $value, $fail) use ($request) {
                $exists = LocationArea::where('city_id', $request->integer('city_id'))
                    ->whereRaw('LOWER(name) = ?', [mb_strtolower($value)])
                    ->exists();
                if ($exists) {
                    $fail(translate('this_area_already_exists'));
                }
            }],
            'city_id' => ['required', 'integer', 'exists:location_cities,id'],
        ]);

        $area = LocationArea::create([
            'city_id' => $request->integer('city_id'),
            'name' => $request->input('name'),
            'is_active' => true,
            'cod_available' => false,
            'sort_order' => 0,
        ]);

        return response()->json([
            'success' => true,
            'area' => $area->only(['id', 'name']),
            'message' => translate('Area_added_successfully'),
        ]);
    }
}
