<?php

namespace App\Http\Controllers;

use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Models\Product;
use App\Models\Business;
use App\Models\Category;
use App\Models\UnitOfMeasurement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request, Business $business)
    {
        $this->authorize('viewAny', [Product::class, $business]);

        $query = Product::where('business_id', $business->id)
            ->with(['category', 'unitOfMeasurement'])
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

        $products = $query->latest()->paginate(15);
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

        return view('business.products.create', compact('business', 'categories', 'units'));
    }

    public function store(StoreProductRequest $request, Business $business): RedirectResponse
    {
        $this->authorize('create', [Product::class, $business]);

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
            'has_variants' => $request->boolean('has_variants', false),
            'allow_backorder' => $request->boolean('allow_backorder', false),
            'images' => $request->images ?? [],
            'attributes' => $request->attributes ?? [],
            'min_stock_level' => $request->min_stock_level ?? 0,
            'max_stock_level' => $request->max_stock_level,
        ]);

        return redirect()->route('business.products.show', [$business, $product])
            ->with('success', 'Product created successfully.');
    }

    public function show(Business $business, Product $product)
    {
        $this->authorize('view', [$product, $business]);

        $product->load(['category', 'unitOfMeasurement', 'variants', 'stockBalances.stockLocation', 'stockMovements' => function ($query) {
            $query->with(['stockLocation', 'user'])->latest()->limit(10);
        }]);

        $currentStock = $product->current_stock;

        if (view()->exists('business.products.show')) {
            return view('business.products.show', compact('business', 'product', 'currentStock'));
        }

        return response()->json(['product' => $product, 'current_stock' => $currentStock]);
    }

    public function edit(Business $business, Product $product): View
    {
        $this->authorize('update', [$business, $product]);

        $categories = Category::where('business_id', $business->id)->where('is_active', true)->get();
        $units = UnitOfMeasurement::where('business_id', $business->id)->where('is_active', true)->get();

        return view('business.products.edit', compact('business', 'product', 'categories', 'units'));
    }

    public function update(UpdateProductRequest $request, Business $business, Product $product): RedirectResponse
    {
        $this->authorize('update', [$business, $product]);

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
            'track_inventory' => $request->boolean('track_inventory', true),
            'is_active' => $request->boolean('is_active', true),
            'has_variants' => $request->boolean('has_variants', false),
            'allow_backorder' => $request->boolean('allow_backorder', false),
            'images' => $request->images ?? [],
            'attributes' => $request->attributes ?? [],
            'min_stock_level' => $request->min_stock_level ?? 0,
            'max_stock_level' => $request->max_stock_level,
        ]);

        return redirect()->route('business.products.show', [$business, $product])
            ->with('success', 'Product updated successfully.');
    }

    public function archive(Business $business, Product $product): RedirectResponse
    {
        $this->authorize('archive', [$business, $product]);

        $product->update(['is_active' => false]);

        return redirect()->route('business.products.index', $business)
            ->with('success', 'Product archived successfully.');
    }

    public function destroy(Business $business, Product $product): RedirectResponse
    {
        $this->authorize('delete', [$business, $product]);

        $product->delete();

        return redirect()->route('business.products.index', $business)
            ->with('success', 'Product deleted successfully.');
    }
}
