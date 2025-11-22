<?php

namespace Database\Seeders;

use App\Models\EarthquakeSourceType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EarthquakeSourceTypeSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        EarthquakeSourceType::updateOrCreate(
            ['key' => 'usgs'],
            ['name' => 'USGS (United States Geological Survey)']
        );

        EarthquakeSourceType::updateOrCreate(
            ['key' => 'emsc'],
            ['name' => 'EMSC (European-Mediterranean Seismological Centre)']
        );
    }
}