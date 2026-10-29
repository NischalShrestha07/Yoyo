<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessModule;
use App\Models\Module;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ModuleController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Module::class);

        $modules = Module::when($request->search, function ($query, $search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        })->latest()->paginate(10);

        return view('platform.modules.index', compact('modules'));
    }

    public function edit(Business $business): View
    {
        $this->authorize('manage', $business);

        $modules = Module::all();
        $businessModules = $business->modules()->withPivot('enabled', 'settings')->get();

        return view('platform.modules.edit', compact('business', 'modules', 'businessModules'));
    }

    public function update(Request $request, Business $business): RedirectResponse
    {
        $this->authorize('manage', $business);

        $request->validate([
            'modules' => ['array'],
            'modules.*.enabled' => ['boolean'],
            'modules.*.settings' => ['nullable', 'array'],
        ]);

        foreach ($request->input('modules', []) as $moduleId => $moduleData) {
            $module = Module::findOrFail($moduleId);
            $businessModule = $business->modules()->where('module_id', $moduleId)->first();

            if ($moduleData['enabled']) {
                if (!$businessModule) {
                    $business->modules()->attach($moduleId, [
                        'enabled' => true,
                        'enabled_by' => auth()->id(),
                        'enabled_at' => now(),
                        'settings' => $moduleData['settings'] ?? [],
                    ]);
                } else {
                    $businessModule->update([
                        'enabled' => true,
                        'enabled_by' => auth()->id(),
                        'enabled_at' => now(),
                        'settings' => $moduleData['settings'] ?? [],
                    ]);
                }
            } else {
                if ($businessModule) {
                    $businessModule->update([
                        'enabled' => false,
                        'disabled_at' => now(),
                    ]);
                }
            }
        }

        return redirect()->route('platform.businesses.show', $business)
            ->with('success', 'Modules updated successfully.');
    }
}