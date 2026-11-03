<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Vendor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VendorController extends Controller
{
    public function index(Request $request, Business $business): View|JsonResponse
    {
        $this->authorize('viewAny', [Vendor::class, $business]);

        $vendors = Vendor::where('business_id', $business->id)->latest()->paginate(20);

        if (view()->exists('business.vendors.index')) {
            return view('business.vendors.index', compact('business', 'vendors'));
        }

        return response()->json(['vendors' => $vendors]);
    }

    public function create(Business $business): View
    {
        $this->authorize('create', [Vendor::class, $business]);
        if (view()->exists('business.vendors.create')) {
            return view('business.vendors.create', compact('business'));
        }
        return view('layouts.app');
    }

    public function store(Request $request, Business $business): RedirectResponse|JsonResponse
    {
        $this->authorize('create', [Vendor::class, $business]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string'],
            'tax_number' => ['nullable', 'string'],
            'address' => ['nullable', 'string'],
        ]);

        $validated['business_id'] = $business->id;

        $vendor = Vendor::create($validated);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'vendor' => $vendor]);
        }

        return redirect()->route('business.vendors.index', $business)->with('success', 'Vendor created successfully.');
    }

    public function show(Business $business, Vendor $vendor): View|JsonResponse
    {
        $this->authorize('view', [$vendor, $business]);
        $vendor->load('bills');
        if (view()->exists('business.vendors.show')) {
            return view('business.vendors.show', compact('business', 'vendor'));
        }
        return response()->json(['vendor' => $vendor]);
    }
}
