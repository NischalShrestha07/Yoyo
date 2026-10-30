<?php

namespace App\Http\Controllers;

use App\Exceptions\Inventory\InsufficientStockException;
use App\Exceptions\Inventory\InvalidStockOperationException;
use App\Http\Requests\Inventory\StockAdjustmentRequest;
use App\Http\Requests\Inventory\StockReceiptRequest;
use App\Http\Requests\Inventory\StockTransferRequest;
use App\Models\Business;
use App\Models\Product;
use App\Models\StockBalance;
use App\Models\StockLocation;
use App\Models\StockMovement;
use App\Models\StockReservation;
use App\Services\Inventory\InventoryConfig;
use App\Services\Inventory\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StockController extends Controller
{
    public function __construct(
        private readonly InventoryService $inventory
    ) {
    }

    public function index(Request $request, Business $business): View
    {
        $this->authorize('viewAny', [StockMovement::class, $business]);

        $locations = StockLocation::where('business_id', $business->id)
            ->where('is_active', true)
            ->orderByDesc('is_default')
            ->get();

        $products = Product::where('business_id', $business->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $selectedLocation = $locations->firstWhere('id', (int) $request->stock_location_id);

        $balances = StockBalance::query()
            ->where('business_id', $business->id)
            ->with(['product', 'productVariant', 'stockLocation'])
            ->when($request->product_id, fn ($query, $productId) => $query->where('product_id', $productId))
            ->when($selectedLocation, fn ($query) => $query->where('stock_location_id', $selectedLocation->id))
            ->when($request->low_stock, fn ($query) => $query->whereHas('product', function ($q) {
                $q->whereColumn('products.min_stock_level', '>=', 'stock_balances.quantity')
                    ->where('products.min_stock_level', '>', 0);
            }))
            ->orderBy('product_id')
            ->paginate(20)
            ->withQueryString();

        $lowStock = Product::query()
            ->where('business_id', $business->id)
            ->where('is_active', true)
            ->where('track_inventory', true)
            ->where('min_stock_level', '>', 0)
            ->withSum('stockBalances as total_stock', 'quantity')
            ->get()
            ->filter(fn (Product $product) => (int) $product->total_stock <= (int) $product->min_stock_level)
            ->values();

        $valuationMethod = $this->inventory->valuation()->method($business);
        $totalValuation = $this->inventory->valuation()->value($business, null, null, $selectedLocation);
        $config = new InventoryConfig($business);

        $reservations = StockReservation::query()
            ->where('business_id', $business->id)
            ->with(['product', 'productVariant', 'stockLocation'])
            ->orderBy('expires_at')
            ->get();

        return view('business.inventory.index', compact(
            'business',
            'balances',
            'locations',
            'products',
            'lowStock',
            'selectedLocation',
            'valuationMethod',
            'totalValuation',
            'config',
            'reservations'
        ));
    }

    public function receiptForm(Business $business): View
    {
        $this->authorize('adjust', [StockMovement::class, $business]);

        $products = Product::where('business_id', $business->id)
            ->where('is_active', true)
            ->where('track_inventory', true)
            ->orderBy('name')
            ->get();
        $locations = StockLocation::where('business_id', $business->id)->where('is_active', true)->get();
        $config = new InventoryConfig($business);

        return view('business.inventory.receipt', compact('business', 'products', 'locations', 'config'));
    }

    public function receipt(StockReceiptRequest $request, Business $business): RedirectResponse
    {
        $this->authorize('adjust', [StockMovement::class, $business]);

        $product = Product::where('business_id', $business->id)->findOrFail($request->product_id);
        $location = StockLocation::where('business_id', $business->id)->findOrFail($request->stock_location_id);

        try {
            $this->inventory->receiveStock($business, $product, (int) $request->quantity, $location, auth()->user(), [
                'product_variant_id' => $request->product_variant_id,
                'unit_cost' => $request->unit_cost,
                'reference' => $request->reference,
                'notes' => $request->notes,
                'batch_info' => $request->batch_info,
                'serial_info' => $this->serials($request),
                'reason' => 'purchase',
            ]);

            return redirect()->route('business.inventory.index', $business)
                ->with('success', 'Stock received successfully.');
        } catch (InsufficientStockException | InvalidStockOperationException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }
    }

    public function adjustmentForm(Business $business): View
    {
        $this->authorize('adjust', [StockMovement::class, $business]);

        $products = Product::where('business_id', $business->id)
            ->where('is_active', true)
            ->where('track_inventory', true)
            ->orderBy('name')
            ->get();
        $locations = StockLocation::where('business_id', $business->id)->where('is_active', true)->get();
        $config = new InventoryConfig($business);

        return view('business.inventory.adjustment', compact('business', 'products', 'locations', 'config'));
    }

    public function adjustment(StockAdjustmentRequest $request, Business $business): RedirectResponse
    {
        $this->authorize('adjust', [StockMovement::class, $business]);

        $product = Product::where('business_id', $business->id)->findOrFail($request->product_id);
        $location = StockLocation::where('business_id', $business->id)->findOrFail($request->stock_location_id);

        try {
            $this->inventory->adjustStock($business, $product, (int) $request->quantity, $location, auth()->user(), [
                'product_variant_id' => $request->product_variant_id,
                'reason' => $request->reason,
                'notes' => $request->notes,
                'unit_cost' => $request->unit_cost,
                'batch_info' => $request->batch_info,
                'serial_info' => $this->serials($request),
            ]);

            return redirect()->route('business.inventory.index', $business)
                ->with('success', 'Stock adjusted successfully.');
        } catch (InsufficientStockException | InvalidStockOperationException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }
    }

    public function transferForm(Business $business): View
    {
        $this->authorize('transfer', [StockMovement::class, $business]);

        $products = Product::where('business_id', $business->id)
            ->where('is_active', true)
            ->where('track_inventory', true)
            ->orderBy('name')
            ->get();
        $locations = StockLocation::where('business_id', $business->id)->where('is_active', true)->get();
        $config = new InventoryConfig($business);

        return view('business.inventory.transfer', compact('business', 'products', 'locations', 'config'));
    }

    public function transfer(StockTransferRequest $request, Business $business): RedirectResponse
    {
        $this->authorize('transfer', [StockMovement::class, $business]);

        $product = Product::where('business_id', $business->id)->findOrFail($request->product_id);
        $fromLocation = StockLocation::where('business_id', $business->id)->findOrFail($request->from_location_id);
        $toLocation = StockLocation::where('business_id', $business->id)->findOrFail($request->to_location_id);

        try {
            $this->inventory->transferStock(
                $business,
                $product,
                (int) $request->quantity,
                $fromLocation,
                $toLocation,
                auth()->user(),
                [
                    'product_variant_id' => $request->product_variant_id,
                    'notes' => $request->notes,
                ]
            );

            return redirect()->route('business.inventory.index', $business)
                ->with('success', 'Stock transferred successfully.');
        } catch (InsufficientStockException | InvalidStockOperationException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }
    }

    public function returnForm(Business $business): View
    {
        $this->authorize('adjust', [StockMovement::class, $business]);

        $products = Product::where('business_id', $business->id)
            ->where('is_active', true)
            ->where('track_inventory', true)
            ->orderBy('name')
            ->get();
        $locations = StockLocation::where('business_id', $business->id)->where('is_active', true)->get();
        $config = new InventoryConfig($business);

        return view('business.inventory.return', compact('business', 'products', 'locations', 'config'));
    }

    public function returnStock(Request $request, Business $business): RedirectResponse
    {
        $this->authorize('adjust', [StockMovement::class, $business]);

        $data = $request->validate([
            'product_id' => ['required', 'integer'],
            'product_variant_id' => ['nullable', 'integer'],
            'stock_location_id' => ['required', 'integer'],
            'quantity' => ['required', 'integer', 'min:1'],
            'reference' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $product = Product::where('business_id', $business->id)->findOrFail($data['product_id']);
        $location = StockLocation::where('business_id', $business->id)->findOrFail($data['stock_location_id']);

        try {
            $this->inventory->returnStock($business, $product, (int) $data['quantity'], $location, auth()->user(), $data);

            return redirect()->route('business.inventory.index', $business)
                ->with('success', 'Stock return recorded successfully.');
        } catch (InsufficientStockException | InvalidStockOperationException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }
    }

    public function movements(Request $request, Business $business): View
    {
        $this->authorize('viewAny', [StockMovement::class, $business]);

        $query = StockMovement::where('business_id', $business->id)
            ->with(['product', 'productVariant', 'stockLocation', 'user', 'reversal', 'reversesMovement'])
            ->when($request->product_id, fn ($query, $productId) => $query->where('product_id', $productId))
            ->when($request->type, fn ($query, $type) => $query->where('type', $type))
            ->when($request->stock_location_id, fn ($query, $locationId) => $query->where('stock_location_id', $locationId))
            ->when($request->from, fn ($query, $from) => $query->where('occurred_at', '>=', $from))
            ->when($request->to, fn ($query, $to) => $query->where('occurred_at', '<=', $to));

        $movements = $query->latest('occurred_at')->latest('id')->paginate(30)->withQueryString();
        $products = Product::where('business_id', $business->id)->orderBy('name')->get();
        $locations = StockLocation::where('business_id', $business->id)->get();

        return view('business.inventory.movements', compact('business', 'movements', 'products', 'locations'));
    }

    public function reverse(Request $request, Business $business, StockMovement $movement): RedirectResponse
    {
        $this->authorize('adjust', [StockMovement::class, $business]);

        abort_unless((int) $movement->business_id === (int) $business->id, 404);

        $data = $request->validate([
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $this->inventory->reverseMovement($business, $movement, auth()->user(), $data['notes'] ?? null);

            return back()->with('success', 'Reversal movement recorded.');
        } catch (InsufficientStockException | InvalidStockOperationException $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }

    public function reserve(Request $request, Business $business): RedirectResponse
    {
        $this->authorize('adjust', [StockMovement::class, $business]);

        $data = $request->validate([
            'product_id' => ['required', 'integer'],
            'product_variant_id' => ['nullable', 'integer'],
            'stock_location_id' => ['required', 'integer'],
            'quantity' => ['required', 'integer', 'min:1'],
            'reference' => ['required', 'string', 'max:255'],
            'expires_at' => ['nullable', 'date', 'after:now'],
        ]);

        $product = Product::where('business_id', $business->id)->findOrFail($data['product_id']);
        $location = StockLocation::where('business_id', $business->id)->findOrFail($data['stock_location_id']);

        try {
            $this->inventory->reserveStock(
                $business,
                $product,
                (int) $data['quantity'],
                $location,
                $data['reference'],
                isset($data['expires_at']) ? \Illuminate\Support\Carbon::parse($data['expires_at']) : null,
                ['product_variant_id' => $data['product_variant_id'] ?? null]
            );

            return back()->with('success', 'Stock reserved successfully.');
        } catch (InsufficientStockException | InvalidStockOperationException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }
    }

    public function releaseReservation(Business $business, StockReservation $reservation): RedirectResponse
    {
        $this->authorize('adjust', [StockMovement::class, $business]);

        abort_unless((int) $reservation->business_id === (int) $business->id, 404);

        $this->inventory->releaseReservation($reservation);

        return back()->with('success', 'Stock reservation released.');
    }

    public function settingsForm(Business $business): View
    {
        $this->authorize('update', $business);

        $config = new InventoryConfig($business);

        return view('business.inventory.settings', compact('business', 'config'));
    }

    public function settingsUpdate(Request $request, Business $business): RedirectResponse
    {
        $this->authorize('update', $business);

        $request->validate([
            'inventory_enforce_stock' => ['boolean'],
            'inventory_multiple_locations' => ['boolean'],
            'inventory_batch_tracking' => ['boolean'],
            'inventory_serial_tracking' => ['boolean'],
            'inventory_reservations_enabled' => ['boolean'],
            'inventory_valuation_method' => ['required', Rule::in(InventoryConfig::VALUATION_METHODS)],
            'products_variants_enabled' => ['boolean'],
            'products_custom_attributes_enabled' => ['boolean'],
            'products_custom_attribute_schema' => ['nullable', 'json'],
        ]);

        $booleans = [
            'inventory.enforce_stock' => 'inventory_enforce_stock',
            'inventory.multiple_locations' => 'inventory_multiple_locations',
            'inventory.batch_tracking' => 'inventory_batch_tracking',
            'inventory.serial_tracking' => 'inventory_serial_tracking',
            'inventory.reservations_enabled' => 'inventory_reservations_enabled',
            'products.variants_enabled' => 'products_variants_enabled',
            'products.custom_attributes_enabled' => 'products_custom_attributes_enabled',
        ];

        foreach ($booleans as $settingKey => $inputKey) {
            $business->setSetting($settingKey, $request->boolean($inputKey), 'bool');
        }

        $business->setSetting(
            'inventory.valuation_method',
            (string) $request->input('inventory_valuation_method'),
            'string'
        );
        $business->setSetting(
            'products.custom_attribute_schema',
            json_decode((string) $request->input('products_custom_attribute_schema', '[]'), true) ?: [],
            'json'
        );

        return redirect()->route('business.inventory.settings', $business)
            ->with('success', 'Inventory settings updated successfully.');
    }

    /**
     * The UI posts one serial per line; the service expects an array.
     */
    private function serials(Request $request): ?array
    {
        if (is_array($request->input('serial_info'))) {
            return array_values(array_filter($request->input('serial_info')));
        }

        $text = trim((string) $request->input('serial_info_text', ''));

        if ($text === '') {
            return null;
        }

        return array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $text))));
    }
}
