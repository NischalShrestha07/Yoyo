<?php

namespace App\Http\Controllers;

use App\Http\Requests\Inventory\StockReceiptRequest;
use App\Http\Requests\Inventory\StockAdjustmentRequest;
use App\Http\Requests\Inventory\StockTransferRequest;
use App\Models\Business;
use App\Models\Product;
use App\Models\StockLocation;
use App\Services\Inventory\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockController extends Controller
{
    public function __construct(
        private InventoryService $inventoryService
    ) {
    }

    public function index(Request $request, Business $business): View
    {
        $this->authorize('viewAny', [\App\Models\StockMovement::class, $business]);

        $locations = StockLocation::where('business_id', $business->id)->where('is_active', true)->get();
        $products = Product::where('business_id', $business->id)->where('is_active', true)->get();

        $query = \App\Models\StockBalance::where('business_id', $business->id)
            ->with(['product', 'productVariant', 'stockLocation'])
            ->when($request->product_id, function ($query, $productId) {
                $query->where('product_id', $productId);
            })
            ->when($request->stock_location_id, function ($query, $locationId) {
                $query->where('stock_location_id', $locationId);
            });

        $balances = $query->paginate(20);

        // Low stock alerts
        $lowStock = \App\Models\StockBalance::where('business_id', $business->id)
            ->with(['product', 'stockLocation'])
            ->whereHas('product', function ($q) {
                $q->where('track_inventory', true);
            })
            ->whereRaw('stock_balances.quantity <= (SELECT min_stock_level FROM products WHERE products.id = stock_balances.product_id)')
            ->get();

        return view('business.inventory.index', compact('business', 'balances', 'locations', 'products', 'lowStock'));
    }

    public function receiptForm(Business $business): View
    {
        $this->authorize('adjust', [\App\Models\StockMovement::class, $business]);

        $products = Product::where('business_id', $business->id)->where('is_active', true)->where('track_inventory', true)->get();
        $locations = StockLocation::where('business_id', $business->id)->where('is_active', true)->get();

        return view('business.inventory.receipt', compact('business', 'products', 'locations'));
    }

    public function receipt(StockReceiptRequest $request, Business $business): RedirectResponse
    {
        $this->authorize('adjust', [\App\Models\StockMovement::class, $business]);

        $product = Product::where('business_id', $business->id)->findOrFail($request->product_id);
        $location = StockLocation::where('business_id', $business->id)->findOrFail($request->stock_location_id);

        try {
            $this->inventoryService->stockReceipt($business, $product, $request->quantity, $location, auth()->user(), [
                'product_variant_id' => $request->product_variant_id,
                'unit_cost' => $request->unit_cost,
                'reference' => $request->reference,
                'notes' => $request->notes,
                'batch_info' => $request->batch_info,
                'serial_info' => $request->serial_info,
                'reason' => 'purchase',
            ]);

            return redirect()->route('business.inventory.index', $business)
                ->with('success', 'Stock received successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function adjustmentForm(Business $business): View
    {
        $this->authorize('adjust', [\App\Models\StockMovement::class, $business]);

        $products = Product::where('business_id', $business->id)->where('is_active', true)->where('track_inventory', true)->get();
        $locations = StockLocation::where('business_id', $business->id)->where('is_active', true)->get();

        return view('business.inventory.adjustment', compact('business', 'products', 'locations'));
    }

    public function adjustment(StockAdjustmentRequest $request, Business $business): RedirectResponse
    {
        $this->authorize('adjust', [\App\Models\StockMovement::class, $business]);

        $product = Product::where('business_id', $business->id)->findOrFail($request->product_id);
        $location = StockLocation::where('business_id', $business->id)->findOrFail($request->stock_location_id);

        try {
            $this->inventoryService->stockAdjustment($business, $product, $request->quantity, $location, auth()->user(), [
                'product_variant_id' => $request->product_variant_id,
                'reason' => $request->reason,
                'notes' => $request->notes,
                'unit_cost' => $request->unit_cost,
                'batch_info' => $request->batch_info,
                'serial_info' => $request->serial_info,
            ]);

            return redirect()->route('business.inventory.index', $business)
                ->with('success', 'Stock adjusted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function transferForm(Business $business): View
    {
        $this->authorize('transfer', [\App\Models\StockMovement::class, $business]);

        $products = Product::where('business_id', $business->id)->where('is_active', true)->where('track_inventory', true)->get();
        $locations = StockLocation::where('business_id', $business->id)->where('is_active', true)->get();

        return view('business.inventory.transfer', compact('business', 'products', 'locations'));
    }

    public function transfer(StockTransferRequest $request, Business $business): RedirectResponse
    {
        $this->authorize('transfer', [\App\Models\StockMovement::class, $business]);

        $product = Product::where('business_id', $business->id)->findOrFail($request->product_id);
        $fromLocation = StockLocation::where('business_id', $business->id)->findOrFail($request->from_location_id);
        $toLocation = StockLocation::where('business_id', $business->id)->findOrFail($request->to_location_id);

        try {
            $this->inventoryService->stockTransfer($business, $product, $request->quantity, $fromLocation, $toLocation, auth()->user(), [
                'product_variant_id' => $request->product_variant_id,
                'notes' => $request->notes,
            ]);

            return redirect()->route('business.inventory.index', $business)
                ->with('success', 'Stock transferred successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function movements(Request $request, Business $business): View
    {
        $this->authorize('viewAny', [\App\Models\StockMovement::class, $business]);

        $query = \App\Models\StockMovement::where('business_id', $business->id)
            ->with(['product', 'productVariant', 'stockLocation', 'user'])
            ->when($request->product_id, function ($query, $productId) {
                $query->where('product_id', $productId);
            })
            ->when($request->type, function ($query, $type) {
                $query->where('type', $type);
            })
            ->when($request->stock_location_id, function ($query, $locationId) {
                $query->where('stock_location_id', $locationId);
            });

        $movements = $query->latest('occurred_at')->paginate(30);
        $products = Product::where('business_id', $business->id)->where('is_active', true)->get();
        $locations = StockLocation::where('business_id', $business->id)->where('is_active', true)->get();

        return view('business.inventory.movements', compact('business', 'movements', 'products', 'locations'));
    }
}
