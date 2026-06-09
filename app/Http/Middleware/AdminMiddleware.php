<?php

namespace App\Http\Middleware;

use App\Utils\AdminLoginRedirect;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if (Auth::guard('admin')->check()) {
            if (Auth::guard('admin')->check() && (Auth::guard('admin')->id() != 1 && Auth::guard('admin')->user()->status != 1)) {
                Auth::guard('admin')->logout();

                return redirect('login/'.AdminLoginRedirect::resolveLoginUrl($request));
            }

            return $next($request);
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'code' => 'auth-001',
                'message' => translate('Unauthorized'),
            ], 401);
        }

        return AdminLoginRedirect::guestRedirect($request);
    }
}
