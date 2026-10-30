<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\UnitOfMeasurement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UnitOfMeasurementController extends Controller
{
    public function index(Request $request, Business $business): View
    {
        $this->authorize('viewAny', [UnitOfMeasurement::class, $business]);

        $units = UnitOfMeasurement::query()
            ->where('business_id', $business->id)
            ->withCount('products')
            ->when($request->search, fn ($query, $search) => $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            }))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('business.units.index', compact('business', 'units'));
    }

    public function create(Business $business): View
    {
        $this->authorize('create', [UnitOfMeasurement::class, $business]);

        return view('business.units.create', compact('business'));
    }

    public function store(Request $request, Business $business): RedirectResponse
    {
        $this->authorize('create', [UnitOfMeasurement::class, $business]);

        $data = $this->validated($request, $business);

        UnitOfMeasurement::create(array_merge($data, ['business_id' => $business->id]));

        return redirect()->route('business.units.index', $business)
            ->with('success', 'Unit of measurement created successfully.');
    }

    public function show(Business $business, UnitOfMeasurement $unit): View
    {
        $this->authorize('view', [$unit, $business]);

        $unit->load('products');

        return view('business.units.show', compact('business', 'unit'));
    }

    public function edit(Business $business, UnitOfMeasurement $unit): View
    {
        $this->authorize('update', [$unit, $business]);

        return view('business.units.edit', compact('business', 'unit'));
    }

    public function update(Request $request, Business $business, UnitOfMeasurement $unit): RedirectResponse
    {
        $this->authorize('update', [$unit, $business]);

        $unit->update($this->validated($request, $business, $unit));

        return redirect()->route('business.units.index', $business)
            ->with('success', 'Unit of measurement updated successfully.');
    }

    public function destroy(Business $business, UnitOfMeasurement $unit): RedirectResponse
    {
        $this->authorize('delete', [$unit, $business]);

        $unit->update(['is_active' => false]);

        return redirect()->route('business.units.index', $business)
            ->with('success', 'Unit of measurement archived successfully.');
    }

    private function validated(Request $request, Business $business, ?UnitOfMeasurement $unit = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required', 'string', 'max:20',
                Rule::unique('units_of_measurement', 'code')
                    ->where('business_id', $business->id)
                    ->ignore($unit?->id),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
        ]);

        $data['is_active'] = $request->has('is_active')
            ? $request->boolean('is_active')
            : ($unit?->is_active ?? true);

        return $data;
    }
}
