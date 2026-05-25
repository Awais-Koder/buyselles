<?php

namespace App\Http\Middleware;

use App\Models\Cart;
use App\Models\GuestUser;
use Closure;
use Illuminate\Http\Request;

class APIGuestMiddleware
{
    public function handle(Request $request, Closure $next): mixed
    {
        if ($request->header('Authorization') && app('auth')->guard('api')) {
            $user = auth('api')->user();

            if ($user) {
                $request->merge(['user' => $user]);

                return $next($request);
            }
        }

        if ($request->guest_id) {
            $guestExists = GuestUser::where('id', $request->guest_id)->exists()
                || Cart::where('customer_id', $request->guest_id)->where('is_guest', 1)->exists();

            if ($guestExists) {
                return $next($request);
            }

            return response()->json(['message' => 'Invalid guest session'], 401);
        }

        return response()->json(['message' => 'Unauthorized'], 401);
    }
}
