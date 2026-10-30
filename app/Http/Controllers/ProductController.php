<?php

namespace App\Http\Controllers;

use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Models\Business;
use App\Models\Category;
use App\Models\Product;
use App\Models\UnitOfMeasurement;
use App\Services\Catalog\ProductAttributeValidator;
use App\Services\Inventory\InventoryConfig;
use App\Services\Inventory\StockValuationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductAttributeValidator $attributeValidator,
        private readonly StockValuationService $valuation
    ) {
    }

    public function index(Request $request, Business $business)
    {
        $this->authorize('viewAny', [Product::class, $business]);

        $query = Product::where('business_id', $business->id)
            ->with(['category', 'unitOfMeasurement'])
            ->withSum('stockBalances as stock_on_hand', 'quantity')
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%");
                });
            })
            ->when($request->category_id, function ($query, $categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->when($request->type, function ($query, $type) {
                $query->where('type', $type);
            })
            ->when($request->has('is_active'), function ($query) use ($request) {
                $query->where('is_active', $request->boolean('is_active'));
            });

        $products = $query->latest()->paginate(15)->withQueryString();
        $categories = Category::where('business_id', $business->id)->where('is_active', true)->get();

        if (view()->exists('business.products.index')) {
            return view('business.products.index', compact('business', 'products', 'categories'));
        }

        return response()->json(['products' => $products]);
    }

    public function create(Business $business): View
    {
        $this->authorize('create', [Product::class, $business]);

        $categories = Category::where('business_id', $business->id)->where('is_active', true)->get();
        $units = UnitOfMeasurement::where('business_id', $business->id)->where('is_active', true)->get();
        $config = new InventoryConfig($business);

        return view('business.products.create', compact('business', 'categories', 'units', 'config'));
    }

    public function store(StoreProductRequest $request, Business $business): RedirectResponse
    {
        $this->authorize('create', [Product::class, $business]);

        $config = new InventoryConfig($business);
        $attributes = $this->attributeValidator->validate($business, $request->input('attributes', []));
        $hasVariants = $request->boolean('has_variants');

        if ($hasVariants && ! $config->variantsEnabled()) {
            throw ValidationException::withMessages([
                'has_variants' => 'Product variants are not enabled for this business.',
            ]);
        }

        $product = Product::create([
            'business_id' => $business->id,
            'category_id' => $request->category_id,
            'unit_of_measurement_id' => $request->unit_of_measurement_id,
            'name' => $request->name,
            'slug' => \Str::slug($request->name),
            'sku' => $request->sku,
            'barcode' => $request->barcode,
            'description' => $request->description,
            'type' => $request->type,
            'purchase_cost' => $request->purchase_cost,
            'selling_price' => $request->selling_price,
            'track_inventory' => $request->boolean('track_inventory', true),
            'is_active' => $request->boolean('is_active', true),
            'has_variants' => $hasVariants,
            'allow_backorder' => $request->boolean('allow_backorder', false),
            'images' => $this->images($request, $business, []),
            'attributes' => $attributes,
            'min_stock_level' => $request->min_stock_level ?? 0,
            'max_stock_level' => $request->max_stock_level,
        ]);

        return redirect()->route('business.products.show', [$business, $product])
            ->with('success', 'Product created successfully.');
    }

    public function show(Business $business, Product $product)
    {
        $this->authorize('view', [$product, $business]);

        $product->load([
            'category',
            'unitOfMeasurement',
            'variants',
            'stockBalances.stockLocation',
            'stockMovements' => fn ($query) => $query->with(['stockLocation', 'user'])->latest('occurred_at')->limit(10),
        ]);

        $currentStock = $product->current_stock;
        $valuation = $product->track_inventory
            ? $this->valuation->value($business, $product)
            : 0.0;

        if (view()->exists('business.products.show')) {
            return view('business.products.show', compact('business', 'product', 'currentStock', 'valuation'));
        }

        return response()->json([
            'product' => $product,
            'current_stock' => $currentStock,
            'valuation' => $valuation,
        ]);
    }

    public function edit(Business $business, Product $product): View
    {
        $this->authorize('update', [$product, $business]);

        $categories = Category::where('business_id', $business->id)->where('is_active', true)->get();
        $units = UnitOfMeasurement::where('business_id', $business->id)->where('is_active', true)->get();
        $config = new InventoryConfig($business);

        return view('business.products.edit', compact('business', 'product', 'categories', 'units', 'config'));
    }

    public function update(UpdateProductRequest $request, Business $business, Product $product): RedirectResponse
    {
        $this->authorize('update', [$product, $business]);

        $config = new InventoryConfig($business);
        $attributes = $this->attributeValidator->validate($business, $request->input('attributes', []));
        $requestedVariants = $request->has('has_variants') ? $request->boolean('has_variants') : null;
        $hasVariants = $requestedVariants ?? $product->has_variants;

        if ($requestedVariants === true && ! $config->variantsEnabled()) {
            throw ValidationException::withMessages([
                'has_variants' => 'Product variants are not enabled for this business.',
            ]);
        }

        if ($requestedVariants === false && $product->variants()->exists()) {
            throw ValidationException::withMessages([
                'has_variants' => 'Archive the existing variants before disabling variants on this product.',
            ]);
        }

        $product->update([
            'category_id' => $request->category_id,
            'unit_of_measurement_id' => $request->unit_of_measurement_id,
            'name' => $request->name,
            'slug' => \Str::slug($request->name),
            'sku' => $request->sku,
            'barcode' => $request->barcode,
            'description' => $request->description,
            'type' => $request->type,
            'purchase_cost' => $request->purchase_cost,
            'selling_price' => $request->selling_price,
            'track_inventory' => $request->has('track_inventory') ? $request->boolean('track_inventory') : $product->track_inventory,
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : $product->is_active,
            'has_variants' => $request->has('has_variants') ? $hasVariants : $product->has_variants,
            'allow_backorder' => $request->has('allow_backorder') ? $request->boolean('allow_backorder') : $product->allow_backorder,
            'images' => $this->images($request, $business, $request->input('images', [])),
            'attributes' => $attributes,
            'min_stock_level' => $request->min_stock_level ?? 0,
            'max_stock_level' => $request->max_stock_level,
        ]);

        return redirect()->route('business.products.show', [$business, $product])
            ->with('success', 'Product updated successfully.');
    }

    public function archive(Business $business, Product $product): RedirectResponse
    {
        $this->authorize('archive', [$product, $business]);

        $product->update(['is_active' => false]);

        return redirect()->route('business.products.index', $business)
            ->with('success', 'Product archived successfully.');
    }

    /**
     * Archiving keeps the identifier reserved so historical movements and
     * documents remain resolvable.
     */
    public function destroy(Business $business, Product $product): RedirectResponse
    {
        $this->authorize('delete', [$product, $business]);

        $product->update(['is_active' => false]);

        return redirect()->route('business.products.index', $business)
            ->with('success', 'Product archived successfully.');
    }

    private function images(Request $request, Business $business, array $existing): array
    {
        $images = array_values(array_filter($existing, fn ($path) => is_string($path) && $path !== ''));

        if ($request->hasFile('image')) {
            $images[] = $request->file('image')->store("businesses/{$business->id}/products", 'public');
        }

        return $images;
    }
}
