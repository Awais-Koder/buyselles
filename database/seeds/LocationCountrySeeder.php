<?php

namespace Database\Seeders;

use App\Models\LocationCountry;
use Illuminate\Database\Seeder;

class LocationCountrySeeder extends Seeder
{
    public function run(): void
    {
        $existing = LocationCountry::pluck('code')->filter()->map(fn ($c) => strtoupper($c))->toArray();

        $toInsert = [];
        $now = now();

        foreach (COUNTRIES as $country) {
            if (in_array(strtoupper($country['code']), $existing, true)) {
                continue;
            }
            $toInsert[] = [
                'name' => $country['name'],
                'code' => $country['code'],
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (! empty($toInsert)) {
            foreach (array_chunk($toInsert, 50) as $chunk) {
                LocationCountry::insert($chunk);
            }
        }
    }
}
