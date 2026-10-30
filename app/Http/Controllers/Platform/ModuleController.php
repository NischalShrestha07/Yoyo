<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessModule;
use App\Models\Module;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $this->authorize('viewAny', $business);

        $modules = Module::all();
        $businessModules = $business->modules()->withPivot('enabled', 'settings')->get();

        return view('platform.modules.edit', compact('business', 'modules', 'businessModules'));
    }

    public function update(Request $request, Business $business): RedirectResponse
    {
        $this->authorize('update', $business);

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
                    $settings = json_encode($moduleData['settings'] ?? []);
                    
                    DB::table('business_modules')->insert([
                        'business_id' => $business->id,
                        'module_id' => $moduleId,
                        'enabled' => true,
                        'enabled_by' => auth()->id(),
                        'enabled_at' => now(),
                        'settings' => $settings,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    $settings = json_encode($moduleData['settings'] ?? []);
                    
                    DB::table('business_modules')
                        ->where('id', $businessModule->id)
                        ->update([
                            'enabled' => true,
                            'enabled_by' => auth()->id(),
                            'enabled_at' => now(),
                            'disabled_at' => null,
                            'settings' => $settings,
                            'updated_at' => now(),
                        ]);
                }
            } else {
                if (!$businessModule) {
                    DB::table('business_modules')->insert([
                        'business_id' => $business->id,
                        'module_id' => $moduleId,
                        'enabled' => false,
                        'disabled_at' => now(),
                        'settings' => json_encode($moduleData['settings'] ?? []),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    DB::table('business_modules')
                        ->where('id', $businessModule->id)
                        ->update([
                            'enabled' => false,
                            'disabled_at' => now(),
                            'updated_at' => now(),
                        ]);
                }
            }
            
            
        }

        return redirect()->route('platform.businesses.show', $business)
            ->with('success', 'Modules updated successfully.');
    }
}