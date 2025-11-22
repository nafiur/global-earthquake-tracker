<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EarthquakeSourceType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SourceTypeController extends Controller
{
    public function index()
    {
        $types = EarthquakeSourceType::withCount('sources')->orderBy('name')->get();
        return view('admin.source-types.index', compact('types'));
    }

    public function create()
    {
        return view('admin.source-types.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'key' => ['required', 'alpha_dash', 'max:50', 'unique:earthquake_source_types,key'],
        ]);

        $type = EarthquakeSourceType::create([
            'name' => $validated['name'],
            'key' => Str::lower($validated['key']),
        ]);

        return redirect()->route('admin.source-types.index')->with('status', "{$type->name} type created.");
    }

    public function edit(EarthquakeSourceType $sourceType)
    {
        return view('admin.source-types.edit', ['type' => $sourceType]);
    }

    public function update(Request $request, EarthquakeSourceType $sourceType)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'key' => ['required', 'alpha_dash', 'max:50', Rule::unique('earthquake_source_types', 'key')->ignore($sourceType->id)],
        ]);

        $sourceType->update([
            'name' => $validated['name'],
            'key' => Str::lower($validated['key']),
        ]);

        return redirect()->route('admin.source-types.index')->with('status', "{$sourceType->name} updated.");
    }

    public function destroy(EarthquakeSourceType $sourceType)
    {
        if ($sourceType->sources()->exists()) {
            return redirect()
                ->route('admin.source-types.index')
                ->with('error', 'Cannot delete a source type that is assigned to existing sources.');
        }

        $sourceType->delete();

        return redirect()->route('admin.source-types.index')->with('status', 'Source type removed.');
    }
}
