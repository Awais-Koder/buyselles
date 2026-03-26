<?php

namespace App\Utils;

use App\Models\LocationCountry;
use Illuminate\Support\Collection;

class LocationManager
{
    /**
     * Get all active countries ordered for header display.
     */
    public static function getActiveCountries(): Collection
    {
        try {
            return LocationCountry::where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'name']);
        } catch (\Throwable) {
            return collect();
        }
    }
}
