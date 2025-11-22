<?php

namespace App\Http\Controllers;

use App\Models\EarthquakeSource;
use App\Models\EarthquakeSourceType;
use Illuminate\Http\Request;

class EarthquakeController extends Controller
{
    public function index()
    {
        $activeSource = EarthquakeSource::with('type')->where('is_active', true)->first();

        return view('earthquake.index', [
            'activeSourceName' => $activeSource?->name,
            'activeSourceType' => $activeSource?->type?->name,
        ]);
    }

    public function getData(Request $request)
    {
        $source = EarthquakeSource::with('type')->where('is_active', true)->first();

        if (!$source || !$source->type) {
            return response()->json(['error' => 'No active data source found'], 500);
        }

        $timeRange = $request->input('timeRange', 'day');
        $minMag = $request->input('minMagnitude', '4.5');

        return match ($source->type->key) {
            EarthquakeSourceType::KEY_USGS => $this->fetchUsgsData($source->url, $timeRange, $minMag),
            EarthquakeSourceType::KEY_EMSC => $this->fetchEmscData($source->url, $timeRange, $minMag),
            default => response()->json(['error' => 'Unsupported source type'], 500),
        };

    }

    private function fetchUsgsData($baseUrl, $timeRange, $minMag)
    {
        $endpoint = $minMag === 'all' 
            ? "{$baseUrl}/all_{$timeRange}.geojson"
            : "{$baseUrl}/{$minMag}_{$timeRange}.geojson";

        try {
            $response = \Illuminate\Support\Facades\Http::get($endpoint);
            return $response->json();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch from USGS'], 500);
        }
    }

    private function fetchEmscData($baseUrl, $timeRange, $minMag)
    {
        // Map time range to start date
        $now = now();
        $startDate = match($timeRange) {
            'hour' => $now->subHour()->toIso8601String(),
            'day' => $now->subDay()->toIso8601String(),
            'week' => $now->subWeek()->toIso8601String(),
            'month' => $now->subMonth()->toIso8601String(),
            default => $now->subDay()->toIso8601String(),
        };

        $minMagValue = $minMag === 'all' ? 1.0 : (float)$minMag;

        try {
            $response = \Illuminate\Support\Facades\Http::get($baseUrl, [
                'start' => $startDate,
                'minmag' => $minMagValue,
                'limit' => 200, // Limit to prevent overload
                'orderby' => 'time'
            ]);

            $data = $response->json();
            
            // Normalize EMSC to USGS format if needed
            // EMSC usually returns GeoJSON similar to USGS, but let's ensure structure
            return $data; 
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch from EMSC'], 500);
        }
    }
}
