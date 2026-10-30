<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessSetting;
use App\Models\PlatformSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', PlatformSetting::class);

        $settings = PlatformSetting::latest()->paginate(10);

        return view('platform.settings.index', compact('settings'));
    }

    public function edit(): View
    {
        $this->authorize('updateAny', PlatformSetting::class);

        $settings = PlatformSetting::all()->keyBy('key');

        return view('platform.settings.edit', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $this->authorize('updateAny', PlatformSetting::class);

        $request->validate([
            'settings' => ['array'],
            'settings.*.key' => ['required', 'string'],
            'settings.*.value' => ['nullable'],
        ]);

        foreach ($request->input('settings', []) as $settingData) {
            PlatformSetting::updateOrCreate(
                ['key' => $settingData['key']],
                ['value' => $settingData['value']]
            );
        }

        return redirect()->route('platform.settings.index')
            ->with('success', 'Settings updated successfully.');
    }

    public function businessEdit(Business $business): View
    {
        $this->authorize('viewAny', $business);

        $settings = $business->settings()->latest()->paginate(10);

        return view('platform.settings.business-edit', compact('business', 'settings'));
    }

    public function businessUpdate(Request $request, Business $business): RedirectResponse
    {
        $this->authorize('update', $business);

        $request->validate([
            'settings' => ['array'],
            'settings.*.key' => ['required', 'string'],
            'settings.*.value' => ['nullable'],
        ]);

        foreach ($request->input('settings', []) as $settingData) {
            BusinessSetting::updateOrCreate(
                ['business_id' => $business->id, 'key' => $settingData['key']],
                ['value' => $settingData['value']]
            );
        }

        return redirect()->route('platform.businesses.show', $business)
            ->with('success', 'Business settings updated successfully.');
    }
}