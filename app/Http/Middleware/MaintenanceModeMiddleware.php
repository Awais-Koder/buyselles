<?php

namespace App\Http\Middleware;

use App\Traits\MaintenanceModeTrait;
use Closure;
use Illuminate\Http\Request;

class MaintenanceModeMiddleware
{
    use MaintenanceModeTrait;

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if ($this->checkMaintenanceMode()) {
            if (request()->is('vendor/*')) {
                return redirect()->route('maintenance-mode', ['maintenance_system' => 'vendor']);
            }

            return redirect()->route('maintenance-mode');
        }

        return $next($request);
    }
}
