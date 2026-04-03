<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\BaseController;
use App\Models\LocationArea;
use App\Models\LocationCity;
use App\Models\LocationCountry;
use App\Models\SellerServiceArea;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;

class LocationController extends BaseController
{
    public function index(?Request $request = null, ?string $type = null): View|Collection|LengthAwarePaginator|null|callable|RedirectResponse|JsonResponse
    {
        return $this->countries($request ?? request());
    }

    // =====================================================================
    //  Full management pages (dedicated CRUD pages under Shop → Locations)
    // =====================================================================

    public function countries(Request $request): View
    {
        $sellerId = auth('seller')->id();
        $searchValue = $request->get('searchValue');

        $countries = LocationCountry::query()
            ->where('seller_id', $sellerId)
            ->when($searchValue, fn($q) => $q->where('name', 'like', "%{$searchValue}%"))
            ->withCount('cities')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(getWebConfig(name: 'pagination_limit'));

        return view('vendor-views.shop.location.country-list', compact('countries', 'searchValue'));
    }

    public function addCountry(Request $request): RedirectResponse
    {
        $sellerId = auth('seller')->id();

        $request->validate([
            'name' => ['required', 'string', 'max:100', function ($attribute, $value, $fail) use ($sellerId) {
                $exists = LocationCountry::where('seller_id', $sellerId)
                    ->whereRaw('LOWER(name) = ?', [mb_strtolower($value)])
                    ->exists();
                if ($exists) {
                    $fail(translate('this_country_already_exists'));
                }
            }],
            'code' => 'nullable|string|max:10',
        ]);

        LocationCountry::create([
            'seller_id' => $sellerId,
            'name' => $request->name,
            'code' => $request->code,
            'is_active' => true,
        ]);

        ToastMagic::success(translate('country_added_successfully'));

        return back();
    }

    public function updateCountry(Request $request, int $id): RedirectResponse
    {
        $sellerId = auth('seller')->id();

        $request->validate([
            'name' => ['required', 'string', 'max:100', function ($attribute, $value, $fail) use ($sellerId, $id) {
                $exists = LocationCountry::where('seller_id', $sellerId)
                    ->whereRaw('LOWER(name) = ?', [mb_strtolower($value)])
                    ->where('id', '!=', $id)
                    ->exists();
                if ($exists) {
                    $fail(translate('this_country_already_exists'));
                }
            }],
            'code' => 'nullable|string|max:10',
        ]);

        LocationCountry::where('id', $id)->where('seller_id', $sellerId)->firstOrFail()->update([
            'name' => $request->name,
            'code' => $request->code,
        ]);

        ToastMagic::success(translate('country_updated_successfully'));

        return back();
    }

    public function deleteCountry(Request $request): RedirectResponse
    {
        LocationCountry::where('id', $request->id)
            ->where('seller_id', auth('seller')->id())
            ->firstOrFail()
            ->delete();
        ToastMagic::success(translate('country_deleted_successfully'));

        return back();
    }

    public function cities(Request $request, int $countryId): View
    {
        $country = LocationCountry::where('id', $countryId)
            ->where('seller_id', auth('seller')->id())
            ->firstOrFail();
        $searchValue = $request->get('searchValue');

        $cities = LocationCity::query()
            ->where('country_id', $countryId)
            ->when($searchValue, fn($q) => $q->where('name', 'like', "%{$searchValue}%"))
            ->withCount('areas')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(getWebConfig(name: 'pagination_limit'));

        return view('vendor-views.shop.location.city-list', compact('country', 'cities', 'searchValue'));
    }

    public function addCity(Request $request): RedirectResponse
    {
        $sellerId = auth('seller')->id();

        $request->validate([
            'country_id' => ['required', Rule::exists('location_countries', 'id')->where('seller_id', $sellerId)],
            'name' => ['required', 'string', 'max:100', function ($attribute, $value, $fail) use ($request) {
                $exists = LocationCity::where('country_id', $request->country_id)
                    ->whereRaw('LOWER(name) = ?', [mb_strtolower($value)])
                    ->exists();
                if ($exists) {
                    $fail(translate('this_city_already_exists'));
                }
            }],
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
        $city = LocationCity::with('country')->findOrFail($id);
        abort_if($city->country->seller_id !== auth('seller')->id(), 403);

        $request->validate([
            'name' => ['required', 'string', 'max:100', function ($attribute, $value, $fail) use ($city, $id) {
                $exists = LocationCity::where('country_id', $city->country_id)
                    ->whereRaw('LOWER(name) = ?', [mb_strtolower($value)])
                    ->where('id', '!=', $id)
                    ->exists();
                if ($exists) {
                    $fail(translate('this_city_already_exists'));
                }
            }],
        ]);

        $city->update(['name' => $request->name]);
        ToastMagic::success(translate('city_updated_successfully'));

        return back();
    }

    public function deleteCity(Request $request): RedirectResponse
    {
        $city = LocationCity::with('country')->findOrFail($request->id);
        abort_if($city->country->seller_id !== auth('seller')->id(), 403);

        $city->delete();
        ToastMagic::success(translate('city_deleted_successfully'));

        return back();
    }

    public function areas(Request $request, int $cityId): View
    {
        $city = LocationCity::with('country')->findOrFail($cityId);
        abort_if($city->country->seller_id !== auth('seller')->id(), 403);

        $searchValue = $request->get('searchValue');

        $areas = LocationArea::query()
            ->where('city_id', $cityId)
            ->when($searchValue, fn($q) => $q->where('name', 'like', "%{$searchValue}%"))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(getWebConfig(name: 'pagination_limit'));

        return view('vendor-views.shop.location.area-list', compact('city', 'areas', 'searchValue'));
    }

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

        $city = LocationCity::with('country')->findOrFail($request->city_id);
        abort_if($city->country->seller_id !== auth('seller')->id(), 403);

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
        $area = LocationArea::with('city.country')->findOrFail($id);
        abort_if($area->city->country->seller_id !== auth('seller')->id(), 403);

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
        $area = LocationArea::with('city.country')->findOrFail($request->id);
        abort_if($area->city->country->seller_id !== auth('seller')->id(), 403);

        $area->delete();
        ToastMagic::success(translate('area_deleted_successfully'));

        return back();
    }

    public function getCitiesByCountry(int $countryId): JsonResponse
    {
        $country = LocationCountry::where('id', $countryId)
            ->where('seller_id', auth('seller')->id())
            ->first();

        if (! $country) {
            return response()->json([]);
        }

        $cities = LocationCity::where('country_id', $countryId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($cities);
    }

    public function getAreasByCity(int $cityId): JsonResponse
    {
        $city = LocationCity::with('country')->find($cityId);
        if (! $city || $city->country->seller_id !== auth('seller')->id()) {
            return response()->json([]);
        }

        $areas = LocationArea::where('city_id', $cityId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'cod_available']);

        return response()->json($areas);
    }

    /**
     * Return distinct countries that this vendor has service areas in.
     * Used by the product form country dropdown.
     */
    public function getProductFormCountries(): JsonResponse
    {
        $countries = LocationCountry::query()
            ->where('seller_id', auth('seller')->id())
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($countries);
    }

    /**
     * Return cities in a country that this vendor has service areas in.
     */
    public function getProductFormCities(string|int $countryId): JsonResponse
    {
        $country = LocationCountry::where('id', $countryId)
            ->where('seller_id', auth('seller')->id())
            ->first();

        if (! $country) {
            return response()->json([]);
        }

        $cities = LocationCity::query()
            ->where('country_id', $countryId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($cities);
    }

    /**
     * Return areas in a city that this vendor has service areas in.
     */
    public function getProductFormAreas(string|int $cityId): JsonResponse
    {
        $city = LocationCity::with('country')->find($cityId);

        if (! $city || $city->country?->seller_id !== auth('seller')->id()) {
            return response()->json([]);
        }

        $areas = LocationArea::query()
            ->where('city_id', $cityId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($areas);
    }

    /**
     * Quick-add a new country (global) from the product form inline modal.
     * The country is created and returned; no service-area link needed at country level.
     */
    public function quickAddCountry(Request $request): JsonResponse
    {
        $sellerId = auth('seller')->id();

        $request->validate([
            'name' => ['required', 'string', 'max:191', function ($attribute, $value, $fail) use ($sellerId) {
                $exists = LocationCountry::where('seller_id', $sellerId)
                    ->whereRaw('LOWER(name) = ?', [mb_strtolower($value)])
                    ->exists();
                if ($exists) {
                    $fail(translate('this_country_already_exists'));
                }
            }],
            'code' => ['nullable', 'string', 'max:10'],
        ]);

        $country = LocationCountry::query()->create([
            'seller_id' => $sellerId,
            'name' => $request->input('name'),
            'code' => strtoupper($request->input('code', '')),
            'is_active' => true,
            'sort_order' => 0,
        ]);

        return response()->json([
            'success' => true,
            'country' => $country->only(['id', 'name']),
            'message' => translate('Country_added_successfully'),
        ]);
    }

    /**
     * Quick-add a new city under a country.
     */
    public function quickAddCity(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:191', function ($attribute, $value, $fail) use ($request) {
                $exists = LocationCity::where('country_id', $request->integer('country_id'))
                    ->whereRaw('LOWER(name) = ?', [mb_strtolower($value)])
                    ->exists();
                if ($exists) {
                    $fail(translate('this_city_already_exists'));
                }
            }],
            'country_id' => ['required', 'integer', Rule::exists('location_countries', 'id')->where('seller_id', auth('seller')->id())],
        ]);

        $city = LocationCity::query()->create([
            'country_id' => $request->integer('country_id'),
            'name' => $request->input('name'),
            'is_active' => true,
            'sort_order' => 0,
        ]);

        return response()->json([
            'success' => true,
            'city' => $city->only(['id', 'name']),
            'message' => translate('City_added_successfully'),
        ]);
    }

    /**
     * Quick-add a new area under a city.
     * Also assigns it to the vendor's service areas automatically.
     */
    public function quickAddArea(Request $request): JsonResponse
    {
        $sellerId = auth('seller')->id();

        $city = LocationCity::with('country')->findOrFail($request->integer('city_id'));
        abort_if($city->country->seller_id !== $sellerId, 403);

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

        $area = LocationArea::query()->create([
            'city_id' => $request->integer('city_id'),
            'name' => $request->input('name'),
            'is_active' => true,
            'cod_available' => false,
            'sort_order' => 0,
        ]);

        // Automatically link to vendor's service areas so it appears in future dropdowns
        SellerServiceArea::query()->firstOrCreate([
            'seller_id' => $sellerId,
            'area_id' => $area->id,
        ]);

        return response()->json([
            'success' => true,
            'area' => $area->only(['id', 'name']),
            'message' => translate('Area_added_successfully'),
        ]);
    }
}
