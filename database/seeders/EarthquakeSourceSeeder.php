<?php

namespace Database\Seeders;

use App\Models\EarthquakeSource;
use App\Models\EarthquakeSourceType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EarthquakeSourceSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $usgsType = EarthquakeSourceType::firstWhere('key', 'usgs');
        $emscType = EarthquakeSourceType::firstWhere('key', 'emsc');

        if ($usgsType) {
            EarthquakeSource::updateOrCreate(
                ['name' => 'USGS (United States Geological Survey)'],
                [
                    'url' => 'https://earthquake.usgs.gov/earthquakes/feed/v1.0/summary',
                    'type_id' => $usgsType->id,
                    'is_active' => true,
                ]
            );
        }

        if ($emscType) {
            EarthquakeSource::updateOrCreate(
                ['name' => 'EMSC (European-Mediterranean Seismological Centre)'],
                [
                    'url' => 'https://www.seismicportal.eu/fdsnws/event/1/query?format=json',
                    'type_id' => $emscType->id,
                    'is_active' => false,
                ]
            );
        }
    }
}
