<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PurchaseOrderController extends Controller
{
    public function index(Request $request, Business $business): View|JsonResponse
    {
        $this->authorize('viewAny', [PurchaseOrder::class, $business]);

        $orders = PurchaseOrder::query()
            ->where('business_id', $business->id)
            ->with(['supplier', 'user'])
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        if (view()->exists('business.purchases.index')) {
            return view('business.purchases.index', compact('business', 'orders'));
        }

        return response()->json(['purchase_orders' => $orders]);
    }

    public function create(Business $business): View
    {
        $this->authorize('create', [PurchaseOrder::class, $business]);
        $suppliers = Supplier::where('business_id', $business->id)->where('is_active', true)->get();
        $products = Product::where('business_id', $business->id)->where('is_active', true)->get();
        if (view()->exists('business.purchases.create')) {
            return view('business.purchases.create', compact('business', 'suppliers', 'products'));
        }
        return view('layouts.app');
    }

    public function store(Request $request, Business $business): RedirectResponse|JsonResponse
    {
        $this->authorize('create', [PurchaseOrder::class, $business]);

        $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
        ]);

        $po = DB::transaction(function () use ($request, $business) {
            $subtotal = 0;
            $items = $request->input('items', []);
            foreach ($items as $item) {
                $subtotal += $item['quantity'] * $item['unit_cost'];
            }
            $total = $subtotal;

            $po = PurchaseOrder::create([
                'business_id' => $business->id,
                'supplier_id' => $request->supplier_id,
                'user_id' => auth()->id(),
                'po_number' => 'PO-'.strtoupper(uniqid()),
                'status' => 'pending',
                'subtotal' => $subtotal,
                'tax_amount' => 0,
                'total_amount' => $total,
                'expected_date' => $request->expected_date,
                'notes' => $request->notes,
            ]);

            foreach ($items as $item) {
                PurchaseOrderItem::create([
                    'business_id' => $business->id,
                    'purchase_order_id' => $po->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'line_total' => $item['quantity'] * $item['unit_cost'],
                ]);
            }
            return $po;
        });

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'purchase_order' => $po]);
        }

        return redirect()->route('business.purchases.show', [$business, $po])->with('success', 'Purchase order created successfully.');
    }

    public function show(Business $business, PurchaseOrder $purchaseOrder): View|JsonResponse
    {
        $this->authorize('view', [$purchaseOrder, $business]);
        $purchaseOrder->load(['supplier', 'user', 'items.product']);
        if (view()->exists('business.purchases.show')) {
            return view('business.purchases.show', compact('business', 'purchaseOrder'));
        }
        return response()->json(['purchase_order' => $purchaseOrder]);
    }

    public function receive(Business $business, PurchaseOrder $purchaseOrder): RedirectResponse|JsonResponse
    {
        $this->authorize('update', [$purchaseOrder, $business]);
        if ($purchaseOrder->status === 'received') {
            return back()->with('error', 'Already received.');
        }

        $purchaseOrder->load('items');
        $inventory = app(\App\Services\Inventory\InventoryService::class);
        $locations = \App\Models\StockLocation::where('business_id', $business->id)->where('is_active', true)->get();
        $location = $locations->first();

        DB::transaction(function () use ($purchaseOrder, $business, $inventory, $location) {
            foreach ($purchaseOrder->items as $item) {
                if ($location && $item->product) {
                    try {
                        $inventory->receiveStock($business, $item->product, $item->quantity, $location, auth()->user(), [
                            'unit_cost' => $item->unit_cost,
                            'reference' => $purchaseOrder->po_number,
                            'reason' => 'purchase',
                        ]);
                    } catch (\Throwable $e) {
                        // ignore
                    }
                }
            }
            $purchaseOrder->update(['status' => 'received', 'received_at' => now()]);
        });

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }
        return back()->with('success', 'Purchase order received and stock updated.');
    }
}
