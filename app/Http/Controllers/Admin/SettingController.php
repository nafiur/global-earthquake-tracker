<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Display the settings form
     */
    public function index()
    {
        $newsApiKey = Setting::get('news_api_key', '');
        
        return view('admin.settings.index', compact('newsApiKey'));
    }

    /**
     * Update settings
     */
    public function update(Request $request)
    {
        $request->validate([
            'news_api_key' => 'nullable|string|max:255'
        ]);

        Setting::set(
            'news_api_key',
            $request->input('news_api_key'),
            'string',
            'NewsAPI.org API key for fetching earthquake-related news'
        );

        return redirect()
            ->route('admin.settings.index')
            ->with('success', 'Settings updated successfully!');
    }

    /**
     * Get news API key for frontend (public endpoint)
     */
    public function getNewsApiKey()
    {
        $apiKey = Setting::get('news_api_key', '');
        
        return response()->json([
            'api_key' => $apiKey
        ]);
    }
}
