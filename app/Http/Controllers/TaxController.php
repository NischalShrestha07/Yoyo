<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Tax;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaxController extends Controller
{
    public function index(Request $request, Business $business): View|JsonResponse
    {
        $this->authorize('viewAny', [Tax::class, $business]);

        $taxes = Tax::where('business_id', $business->id)->latest()->paginate(20);

        if (view()->exists('business.taxes.index')) {
            return view('business.taxes.index', compact('business', 'taxes'));
        }

        return response()->json(['taxes' => $taxes]);
    }

    public function create(Business $business): View
    {
        $this->authorize('create', [Tax::class, $business]);
        if (view()->exists('business.taxes.create')) {
            return view('business.taxes.create', compact('business'));
        }
        return view('layouts.app');
    }

    public function store(Request $request, Business $business): RedirectResponse|JsonResponse
    {
        $this->authorize('create', [Tax::class, $business]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'rate' => ['required', 'numeric', 'min:0'],
            'type' => ['required', 'in:percentage,fixed'],
        ]);

        $validated['business_id'] = $business->id;
        $validated['code'] = $request->code;
        $validated['is_default'] = $request->boolean('is_default');
        $validated['is_active'] = $request->boolean('is_active', true);

        if ($validated['is_default']) {
            Tax::where('business_id', $business->id)->update(['is_default' => false]);
        }

        $tax = Tax::create($validated);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'tax' => $tax]);
        }

        return redirect()->route('business.taxes.index', $business)->with('success', 'Tax created successfully.');
    }
}
