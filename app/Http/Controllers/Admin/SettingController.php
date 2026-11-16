<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    // Display settings
    public function index()
    {
        $maxClubs = AppSetting::where('key', 'max_clubs_per_student')
            ->value('value') ?? 3;

        return view('admin.settings', compact('maxClubs'));
    }

    // Update settings
    public function update(Request $request)
    {
        $validated = $request->validate([
            'max_clubs' => 'required|integer|min:1|max:20',
        ]);

        AppSetting::updateOrCreate(
            ['key' => 'max_clubs_per_student'],
            ['value' => $validated['max_clubs']]
        );

        return redirect('/admin/settings')
            ->with('success', 'Settings updated successfully.');
    }
}