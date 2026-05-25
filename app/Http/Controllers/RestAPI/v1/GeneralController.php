<?php

namespace App\Http\Controllers\RestAPI\v1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\LocationArea;
use App\Models\LocationCity;
use App\Models\LocationCountry;
use App\Models\Product;
use App\Models\Seller;
use App\Utils\Helpers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GeneralController extends Controller
{
    public function get_categories(): JsonResponse
    {
        $categories = Category::where(['position' => 0])->priority()->get();

        return response()->json($categories, 200);
    }

    public function get_countries(Request $request): JsonResponse
    {
        $search = $request->get('search');
        $query = LocationCountry::query();

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        } else {
            // Default to showing active countries or a subset
            $query->where('is_active', true);
        }

        $countries = $query->orderBy('sort_order', 'asc')
            ->orderBy('name', 'asc')
            ->limit(100) // Increase limit to show more countries
            ->get();

        return response()->json($countries, 200);
    }

    public function get_cities(Request $request, int $country_id): JsonResponse
    {
        $search = $request->get('search');

        $query = LocationCity::query()->where('country_id', $country_id);

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        return response()->json($query->orderBy('name')->get(), 200);
    }

    public function get_areas(Request $request, int $city_id): JsonResponse
    {
        $search = $request->get('search');

        $query = LocationArea::query()->where('city_id', $city_id);

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        return response()->json($query->orderBy('name')->get(), 200);
    }

    public function get_discovery_products(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 10);
        $offset = $request->get('offset', 1);

        $products = Product::active()
            ->when($request->has('category_id'), function ($query) use ($request) {
                $query->where('category_id', $request['category_id']);
            })
            ->when($request->has('search'), function ($query) use ($request) {
                $query->where('name', 'like', "%{$request['search']}%");
            })
            ->when($request->hasAny(['country_id', 'city_id', 'area_id']), function ($query) use ($request) {
                $query->where(function ($subQuery) use ($request) {
                    $subQuery->whereHas('shop', function ($query) use ($request) {
                        if ($request->has('country_id')) {
                            $query->where('store_country_id', $request['country_id']);
                        }
                        if ($request->has('city_id')) {
                            $query->where('store_city_id', $request['city_id']);
                        }
                        if ($request->has('area_id')) {
                            $query->where('store_area_id', $request['area_id']);
                        }
                    })
                        ->orWhere('product_type', 'digital')
                        ->orWhereHas('category', function ($categoryQuery) {
                            $categoryQuery->where('category_type', 'digital');
                        });
                });
            })
            ->withCount(['orderDetails', 'reviews', 'wishList'])
            ->with(['reviews', 'rating', 'shop'])
            ->orderBy('order_details_count', 'desc') // Best Selling Priority
            ->paginate($limit, ['*'], 'page', $offset);

        return response()->json([
            'total_size' => $products->total(),
            'limit' => (int) $limit,
            'offset' => (int) $offset,
            'products' => Helpers::product_data_formatting($products->items(), true),
        ], 200);
    }

    public function get_discovery_vendors(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 10);
        $offset = $request->get('offset', 1);

        $vendors = Seller::approved()
            ->with(['shop'])
            ->whereHas('shop', function ($query) use ($request) {
                if ($request->has('country_id')) {
                    $query->where('store_country_id', $request['country_id']);
                }
                if ($request->has('city_id')) {
                    $query->where('store_city_id', $request['city_id']);
                }
                if ($request->has('area_id')) {
                    $query->where('store_area_id', $request['area_id']);
                }
                if ($request->has('search')) {
                    $query->where('name', 'like', "%{$request['search']}%");
                }
            })
            ->when($request->has('category_id'), function ($query) use ($request) {
                $query->whereHas('product', function ($productQuery) use ($request) {
                    $productQuery->active()
                        ->where('category_id', $request['category_id']);
                });
            })
            ->withCount(['product' => function ($query) {
                $query->active();
            }])
            ->paginate($limit, ['*'], 'page', $offset);

        $vendors->getCollection()->transform(function ($seller) {
            $seller['average_rating'] = \App\Models\Review::active()->whereHas('product', function ($query) use ($seller) {
                $query->where('user_id', $seller->id)->where('added_by', 'seller');
            })->avg('rating') ?? 0;
            $seller['rating_count'] = \App\Models\Review::active()->whereHas('product', function ($query) use ($seller) {
                $query->where('user_id', $seller->id)->where('added_by', 'seller');
            })->count();

            return $seller;
        });

        return response()->json([
            'total_size' => $vendors->total(),
            'limit' => (int) $limit,
            'offset' => (int) $offset,
            'sellers' => $vendors->items(),
        ], 200);
    }
}
