<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\StockLocation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StockLocationController extends Controller
{
    public function index(Business $business)
    {
        $this->authorize('viewAny', [StockLocation::class, $business]);

        $locations = StockLocation::query()
            ->where('business_id', $business->id)
            ->withCount('stockBalances')
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->paginate(15);

        if (view()->exists('business.stock-locations.index')) {
            return view('business.stock-locations.index', compact('business', 'locations'));
        }

        return response()->json(['locations' => $locations]);
    }

    public function create(Business $business): View
    {
        $this->authorize('create', [StockLocation::class, $business]);

        return view('business.stock-locations.create', compact('business'));
    }

    public function store(Request $request, Business $business): RedirectResponse
    {
        $this->authorize('create', [StockLocation::class, $business]);

        $data = $this->validated($request, $business);

        $location = StockLocation::create(array_merge($data, [
            'business_id' => $business->id,
        ]));

        if ($location->is_default) {
            $this->makeDefault($business, $location);
        }

        return redirect()->route('business.stock-locations.index', $business)
            ->with('success', 'Stock location created successfully.');
    }

    public function show(Business $business, StockLocation $stockLocation)
    {
        $this->authorize('view', [$stockLocation, $business]);

        $stockLocation->load(['stockBalances.product', 'stockBalances.productVariant']);

        if (view()->exists('business.stock-locations.show')) {
            return view('business.stock-locations.show', compact('business', 'stockLocation'));
        }

        return response()->json(['stock_location' => $stockLocation]);
    }

    public function edit(Business $business, StockLocation $stockLocation): View
    {
        $this->authorize('update', [$stockLocation, $business]);

        return view('business.stock-locations.edit', compact('business', 'stockLocation'));
    }

    public function update(Request $request, Business $business, StockLocation $stockLocation): RedirectResponse
    {
        $this->authorize('update', [$stockLocation, $business]);

        $data = $this->validated($request, $business, $stockLocation);
        $stockLocation->update($data);

        if ($stockLocation->is_default) {
            $this->makeDefault($business, $stockLocation);
        }

        return redirect()->route('business.stock-locations.index', $business)
            ->with('success', 'Stock location updated successfully.');
    }

    /**
     * Locations are archived rather than deleted so stock history keeps its
     * location reference.
     */
    public function destroy(Business $business, StockLocation $stockLocation): RedirectResponse
    {
        $this->authorize('delete', [$stockLocation, $business]);

        $stockLocation->update(['is_active' => false, 'is_default' => false]);

        return redirect()->route('business.stock-locations.index', $business)
            ->with('success', 'Stock location archived successfully.');
    }

    private function validated(Request $request, Business $business, ?StockLocation $location = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required', 'string', 'max:50',
                Rule::unique('stock_locations', 'code')
                    ->where('business_id', $business->id)
                    ->ignore($location?->id),
            ],
            'address' => ['nullable', 'string', 'max:1000'],
            'is_default' => ['boolean'],
            'is_active' => ['boolean'],
        ]);

        $data['is_default'] = $request->has('is_default')
            ? $request->boolean('is_default')
            : ($location?->is_default ?? false);
        $data['is_active'] = $request->has('is_active')
            ? $request->boolean('is_active')
            : ($location?->is_active ?? true);

        return $data;
    }

    private function makeDefault(Business $business, StockLocation $location): void
    {
        StockLocation::query()
            ->where('business_id', $business->id)
            ->whereKeyNot($location->id)
            ->update(['is_default' => false]);
    }
}
