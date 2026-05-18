<?php

namespace Database\Seeders;

use App\Models\LocationCountry;
use Illuminate\Database\Seeder;

class LocationCountrySeeder extends Seeder
{
    public function run(): void
    {
        $existingCountries = LocationCountry::all()->keyBy(function ($country) {
            return strtoupper(trim($country->code));
        });

        $toInsert = [];
        $now = now();

        foreach (COUNTRIES as $country) {
            $code = strtoupper(trim($country['code']));
            $name = trim($country['name']);

            if ($existingCountries->has($code)) {
                $existingCountry = $existingCountries->get($code);
                if ($existingCountry->name !== $name) {
                    $existingCountry->update([
                        'name' => $name,
                    ]);
                }
            } else {
                $toInsert[] = [
                    'name' => $name,
                    'code' => $country['code'],
                    'is_active' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if (! empty($toInsert)) {
            foreach (array_chunk($toInsert, 50) as $chunk) {
                LocationCountry::insert($chunk);
            }
        }
    }
}
