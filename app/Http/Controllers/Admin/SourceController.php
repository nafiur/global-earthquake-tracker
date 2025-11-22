<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EarthquakeSource;
use App\Models\EarthquakeSourceType;
use Illuminate\Http\Request;

class SourceController extends Controller
{
    public function index()
    {
        $sources = EarthquakeSource::with('type')->get();
        return view('dashboard', compact('sources'));
    }

    public function create()
    {
        $types = EarthquakeSourceType::orderBy('name')->get();
        return view('admin.sources.create', compact('types'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'url' => ['required', 'url'],
            'type_id' => ['required', 'exists:earthquake_source_types,id'],
        ]);

        $shouldActivate = $request->boolean('is_active');

        if ($shouldActivate) {
            EarthquakeSource::query()->update(['is_active' => false]);
        }

        $source = EarthquakeSource::create([
            'name' => $validated['name'],
            'url' => $validated['url'],
            'type_id' => $validated['type_id'],
            'is_active' => $shouldActivate,
        ]);

        return redirect()->route('dashboard')->with('status', "{$source->name} created.");
    }

    public function edit(EarthquakeSource $source)
    {
        $types = EarthquakeSourceType::orderBy('name')->get();
        return view('admin.sources.edit', compact('source', 'types'));
    }

    public function update(Request $request, EarthquakeSource $source)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'url' => ['required', 'url'],
            'type_id' => ['required', 'exists:earthquake_source_types,id'],
        ]);

        $shouldActivate = $request->boolean('is_active');

        $source->update([
            'name' => $validated['name'],
            'url' => $validated['url'],
            'type_id' => $validated['type_id'],
        ]);

        if ($shouldActivate) {
            EarthquakeSource::query()->update(['is_active' => false]);
            $source->update(['is_active' => true]);
        }

        return redirect()->route('dashboard')->with('status', "{$source->name} settings updated.");
    }

    public function toggle(EarthquakeSource $source)
    {
        EarthquakeSource::query()->update(['is_active' => false]);
        $source->update(['is_active' => true]);

        return redirect()->back()->with('status', "Switched to {$source->name}");
    }
}
