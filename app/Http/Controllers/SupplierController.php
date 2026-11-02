<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(Request $request, Business $business): View|JsonResponse
    {
        $this->authorize('viewAny', [Supplier::class, $business]);

        $suppliers = Supplier::query()
            ->where('business_id', $business->id)
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        if (view()->exists('business.suppliers.index')) {
            return view('business.suppliers.index', compact('business', 'suppliers'));
        }

        return response()->json(['suppliers' => $suppliers]);
    }

    public function create(Business $business): View
    {
        $this->authorize('create', [Supplier::class, $business]);
        if (view()->exists('business.suppliers.create')) {
            return view('business.suppliers.create', compact('business'));
        }
        return view('layouts.app');
    }

    public function store(Request $request, Business $business): RedirectResponse|JsonResponse
    {
        $this->authorize('create', [Supplier::class, $business]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'tax_id' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ]);

        $validated['business_id'] = $business->id;
        $validated['is_active'] = $request->boolean('is_active', true);

        $supplier = Supplier::create($validated);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'supplier' => $supplier]);
        }

        return redirect()->route('business.suppliers.index', $business)->with('success', 'Supplier created successfully.');
    }

    public function show(Business $business, Supplier $supplier): View|JsonResponse
    {
        $this->authorize('view', [$supplier, $business]);
        $supplier->load('purchaseOrders');
        if (view()->exists('business.suppliers.show')) {
            return view('business.suppliers.show', compact('business', 'supplier'));
        }
        return response()->json(['supplier' => $supplier]);
    }

    public function edit(Business $business, Supplier $supplier): View
    {
        $this->authorize('update', [$supplier, $business]);
        if (view()->exists('business.suppliers.edit')) {
            return view('business.suppliers.edit', compact('business', 'supplier'));
        }
        return view('layouts.app');
    }

    public function update(Request $request, Business $business, Supplier $supplier): RedirectResponse|JsonResponse
    {
        $this->authorize('update', [$supplier, $business]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'tax_id' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ]);

        $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : $supplier->is_active;
        $supplier->update($validated);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'supplier' => $supplier]);
        }

        return redirect()->route('business.suppliers.index', $business)->with('success', 'Supplier updated successfully.');
    }

    public function destroy(Business $business, Supplier $supplier): RedirectResponse|JsonResponse
    {
        $this->authorize('delete', [$supplier, $business]);
        $supplier->update(['is_active' => false]);
        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }
        return redirect()->route('business.suppliers.index', $business)->with('success', 'Supplier archived successfully.');
    }
}
