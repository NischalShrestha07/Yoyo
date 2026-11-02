<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request, Business $business): View|JsonResponse
    {
        $this->authorize('viewAny', [Customer::class, $business]);

        $customers = Customer::query()
            ->where('business_id', $business->id)
            ->when($request->search, function ($q, $search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        if (view()->exists('business.customers.index')) {
            return view('business.customers.index', compact('business', 'customers'));
        }

        return response()->json(['customers' => $customers]);
    }

    public function create(Business $business): View
    {
        $this->authorize('create', [Customer::class, $business]);
        if (view()->exists('business.customers.create')) {
            return view('business.customers.create', compact('business'));
        }
        return view('layouts.app');
    }

    public function store(Request $request, Business $business): RedirectResponse|JsonResponse
    {
        $this->authorize('create', [Customer::class, $business]);

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $validated['business_id'] = $business->id;
        $validated['is_active'] = $request->boolean('is_active', true);

        $customer = Customer::create($validated);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'customer' => $customer]);
        }

        return redirect()->route('business.customers.index', $business)->with('success', 'Customer created successfully.');
    }

    public function show(Business $business, Customer $customer): View|JsonResponse
    {
        $this->authorize('view', [$customer, $business]);
        $customer->load('sales');
        if (view()->exists('business.customers.show')) {
            return view('business.customers.show', compact('business', 'customer'));
        }
        return response()->json(['customer' => $customer]);
    }

    public function edit(Business $business, Customer $customer): View
    {
        $this->authorize('update', [$customer, $business]);
        if (view()->exists('business.customers.edit')) {
            return view('business.customers.edit', compact('business', 'customer'));
        }
        return view('layouts.app');
    }

    public function update(Request $request, Business $business, Customer $customer): RedirectResponse|JsonResponse
    {
        $this->authorize('update', [$customer, $business]);

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : $customer->is_active;
        $customer->update($validated);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'customer' => $customer]);
        }

        return redirect()->route('business.customers.index', $business)->with('success', 'Customer updated successfully.');
    }

    public function destroy(Business $business, Customer $customer): RedirectResponse|JsonResponse
    {
        $this->authorize('delete', [$customer, $business]);
        $customer->update(['is_active' => false]);
        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }
        return redirect()->route('business.customers.index', $business)->with('success', 'Customer archived successfully.');
    }
}
