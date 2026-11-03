<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BusinessSettingsController extends Controller
{
    public function index(Business $business): View|JsonResponse
    {
        $this->authorize('manage-business', $business);

        $settings = [
            'sales.tax_rate' => $business->getSetting('sales.tax_rate', 0),
            'sales.allow_partial_payments' => $business->getSetting('sales.allow_partial_payments', false),
            'inventory.enforce_stock' => $business->getSetting('inventory.enforce_stock', true),
            'inventory.valuation_method' => $business->getSetting('inventory.valuation_method', 'weighted_average'),
            'products.variants_enabled' => $business->getSetting('products.variants_enabled', false),
        ];

        if (view()->exists('business.settings.index')) {
            return view('business.settings.index', compact('business', 'settings'));
        }

        return response()->json($settings);
    }

    public function update(Request $request, Business $business): RedirectResponse|JsonResponse
    {
        $this->authorize('manage-business', $business);

        $data = $request->all();

        foreach ($data as $key => $value) {
            if (in_array($key, ['_token', '_method'])) {
                continue;
            }
            if (is_bool($value) || $value === '0' || $value === '1') {
                $business->setSetting($key, (bool)$value, 'bool');
            } elseif (is_numeric($value) && strpos($key, 'rate') !== false) {
                $business->setSetting($key, $value, 'float');
            } else {
                $business->setSetting($key, $value, 'string');
            }
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Settings updated successfully.');
    }
}
