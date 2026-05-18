<?php

namespace Tests\Feature;

use App\Models\LocationCountry;
use Database\Seeders\LocationCountrySeeder;
use Tests\TestCase;

class GlobalLocationCountriesTest extends TestCase
{
    /**
     * Test that running LocationCountrySeeder successfully populates all countries.
     */
    public function test_location_countries_can_be_seeded(): void
    {
        $this->seed(LocationCountrySeeder::class);

        // Check that a large number of countries got populated (there are 245 countries in app/Library/Constant.php)
        $this->assertGreaterThan(200, LocationCountry::count());
    }

    /**
     * Test that seeded countries are set as active by default so they appear in filters.
     */
    public function test_all_seeded_countries_are_active(): void
    {
        $totalCount = LocationCountry::count();
        $activeCount = LocationCountry::where('is_active', true)->count();

        $this->assertEquals($totalCount, $activeCount);
    }
}
