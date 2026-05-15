<?php

namespace App\Http\Controllers\RestAPI\v1;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\GuestUser;
use App\Models\HelpTopic;
use App\Utils\Helpers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GeneralController extends Controller
{
    public function faq(): JsonResponse
    {
        return response()->json(HelpTopic::orderBy('ranking')->get(), 200);
    }

    public function get_guest_id(Request $request): JsonResponse
    {
        $guestId = GuestUser::create([
            'ip_address' => $request->ip(),
            'created_at' => now(),
        ]);

        return response()->json(['guest_id' => $guestId?->id], 200);
    }

    public function contact_store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'mobile_number' => 'required',
            'subject' => 'required',
            'message' => 'required',
            'email' => 'required',
            'name' => 'required',
        ], [
            'name.required' => 'Name is Empty!',
            'mobile_number.required' => 'Mobile Number is Empty!',
            'subject.required' => ' Subject is Empty!',
            'message.required' => 'Message is Empty!',
            'email.required' => 'Email is Empty!',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        Contact::create([
            'name' => $request['name'],
            'email' => $request['email'],
            'mobile_number' => $request['mobile_number'],
            'subject' => $request['subject'],
            'message' => $request['message'],
        ]);

        return response()->json(['message' => 'your_message_send_successfully'], 200);
    }

    public function get_countries(): JsonResponse
    {
        return response()->json(\App\Models\LocationCountry::where('is_active', true)->orderBy('sort_order')->get(), 200);
    }

    public function get_cities($country_id): JsonResponse
    {
        return response()->json(\App\Models\LocationCity::where(['country_id' => $country_id, 'is_active' => true])->orderBy('sort_order')->get(), 200);
    }

    public function get_areas($city_id): JsonResponse
    {
        return response()->json(\App\Models\LocationArea::where(['city_id' => $city_id, 'is_active' => true])->orderBy('sort_order')->get(), 200);
    }

    public function get_discovery_vendors(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 10);
        $offset = $request->get('offset', 1);

        $vendors = \App\Models\Seller::approved()
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
            'sellers' => $vendors->values(),
        ], 200);
    }

    public function get_discovery_products(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 10);
        $offset = $request->get('offset', 1);

        $products = \App\Models\Product::active()
            ->when($request->has('category_id'), function ($query) use ($request) {
                $query->where('category_id', $request['category_id']);
            })
            ->whereHas('seller.shop', function ($query) use ($request) {
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
            ->with(['reviews', 'rating'])
            ->latest()
            ->paginate($limit, ['*'], 'page', $offset);

        return response()->json([
            'total_size' => $products->total(),
            'limit' => (int) $limit,
            'offset' => (int) $offset,
            'products' => Helpers::product_data_formatting($products->items(), true),
        ], 200);
    }
}
