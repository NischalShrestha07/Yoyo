<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\StockLocation;
use App\Services\Inventory\InventoryConfig;
use App\Services\Sales\SalesService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PosController extends Controller
{
    public function __construct(
        private readonly SalesService $sales
    ) {
    }

    public function index(Business $business): View|JsonResponse
    {
        $this->authorize('viewAny', [Sale::class, $business]);

        $products = Product::query()
            ->where('business_id', $business->id)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->where('track_inventory', false)->orWhereHas('stockBalances');
            })
            ->orWhere(function ($q) use ($business) {
                $q->where('business_id', $business->id)->where('is_active', true)->where('track_inventory', false);
            })
            ->where('business_id', $business->id)
            ->where('is_active', true)
            ->with(['category'])
            ->limit(100)
            ->get();

        $locations = StockLocation::where('business_id', $business->id)->where('is_active', true)->get();
        $customers = Customer::where('business_id', $business->id)->where('is_active', true)->get();
        $config = new InventoryConfig($business);
        $taxRate = (float) ($business->getSetting('sales.tax_rate', 0) ?? 0);

        if (view()->exists('business.pos.index')) {
            return view('business.pos.index', compact('business', 'products', 'locations', 'customers', 'config', 'taxRate'));
        }

        return response()->json(['products' => $products, 'locations' => $locations, 'customers' => $customers]);
    }

    public function search(Business $business, Request $request): JsonResponse
    {
        $this->authorize('viewAny', [Sale::class, $business]);

        $search = $request->input('q', $request->input('search', ''));
        $location = null;
        if ($request->stock_location_id) {
            $location = StockLocation::where('business_id', $business->id)->find($request->stock_location_id);
        }

        $results = $this->sales->searchProducts($business, $search, $location);

        return response()->json(['products' => $results]);
    }

    public function checkout(Request $request, Business $business): JsonResponse|RedirectResponse
    {
        $this->authorize('create', [Sale::class, $business]);

        $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $sale = $this->sales->createSale($business, auth()->user(), $request->all());
            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'sale' => $sale->load(['items', 'payments'])]);
            }
            return redirect()->route('business.pos.receipt', [$business, $sale])->with('success', 'Sale completed successfully.');
        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'errors' => $e->errors()], 422);
            }
            return back()->withInput()->withErrors($e->errors());
        } catch (\Throwable $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function receipt(Business $business, Sale $sale): View|JsonResponse
    {
        $this->authorize('view', [$sale, $business]);

        $sale->load(['items', 'payments', 'customer', 'user']);

        if (view()->exists('business.pos.receipt')) {
            return view('business.pos.receipt', compact('business', 'sale'));
        }

        return response()->json(['sale' => $sale]);
    }

    public function print(Business $business, Sale $sale): View|JsonResponse
    {
        $this->authorize('view', [$sale, $business]);

        $sale->load(['items', 'payments', 'customer', 'user']);

        if (view()->exists('business.pos.print')) {
            return view('business.pos.print', compact('business', 'sale'));
        }

        if (view()->exists('business.pos.receipt')) {
            return view('business.pos.receipt', compact('business', 'sale'));
        }

        return response()->json(['sale' => $sale]);
    }
}
