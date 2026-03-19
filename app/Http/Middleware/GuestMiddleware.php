<?php

namespace App\Http\Middleware;

use App\Models\GuestUser;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GuestMiddleware
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (! Auth::guard('customer')->check()) {
            if (! session('guest_id')) {
                $guestId = GuestUser::create([
                    'ip_address' => $request->ip(),
                    'created_at' => now(),
                ]);

                session()->put('guest_id', $guestId?->id);
            }
        }

        return $next($request);
    }
}
