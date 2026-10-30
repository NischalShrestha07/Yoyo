<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\Inventory\InventoryConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProductVariantController extends Controller
{
    public function index(Business $business, Product $product)
    {
        $this->authorize('view', [$product, $business]);

        $variants = $product->variants()->latest()->paginate(20);

        if (view()->exists('business.products.variants.index')) {
            return view('business.products.variants.index', compact('business', 'product', 'variants'));
        }

        return response()->json(['variants' => $variants]);
    }

    public function create(Business $business, Product $product): View
    {
        $this->authorize('update', [$product, $business]);
        $this->assertVariantsEnabled($business, $product);

        return view('business.products.variants.create', compact('business', 'product'));
    }

    public function store(Request $request, Business $business, Product $product): RedirectResponse
    {
        $this->authorize('update', [$product, $business]);
        $this->assertVariantsEnabled($business, $product);

        $data = $this->validated($request, $business, $product);

        $product->variants()->create(array_merge($data, [
            'business_id' => $business->id,
        ]));

        return redirect()->route('business.products.show', [$business, $product])
            ->with('success', 'Product variant created successfully.');
    }

    public function edit(Business $business, Product $product, ProductVariant $variant): View
    {
        $this->authorize('update', [$product, $business]);
        $this->assertVariantBelongs($product, $variant);
        $this->assertVariantsEnabled($business, $product);

        return view('business.products.variants.edit', compact('business', 'product', 'variant'));
    }

    public function update(Request $request, Business $business, Product $product, ProductVariant $variant): RedirectResponse
    {
        $this->authorize('update', [$product, $business]);
        $this->assertVariantBelongs($product, $variant);
        $this->assertVariantsEnabled($business, $product);

        $variant->update($this->validated($request, $business, $product, $variant));

        return redirect()->route('business.products.show', [$business, $product])
            ->with('success', 'Product variant updated successfully.');
    }

    public function destroy(Business $business, Product $product, ProductVariant $variant): RedirectResponse
    {
        $this->authorize('update', [$product, $business]);
        $this->assertVariantBelongs($product, $variant);

        $variant->update(['is_active' => false]);

        return redirect()->route('business.products.show', [$business, $product])
            ->with('success', 'Product variant archived successfully.');
    }

    private function validated(Request $request, Business $business, Product $product, ?ProductVariant $variant = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => [
                'required', 'string', 'max:255',
                Rule::unique('product_variants', 'sku')
                    ->where('business_id', $business->id)
                    ->ignore($variant?->id),
            ],
            'barcode' => [
                'nullable', 'string', 'max:255',
                Rule::unique('product_variants', 'barcode')
                    ->where('business_id', $business->id)
                    ->ignore($variant?->id),
            ],
            'purchase_cost' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'attributes' => ['nullable', 'array'],
            'is_active' => ['boolean'],
        ]) + [
            'is_active' => true,
        ];
    }

    private function assertVariantsEnabled(Business $business, Product $product): void
    {
        $config = new InventoryConfig($business);

        if (! $config->variantsEnabled()) {
            throw ValidationException::withMessages([
                'variants' => 'Product variants are not enabled for this business.',
            ]);
        }

        if (! $product->has_variants) {
            throw ValidationException::withMessages([
                'variants' => 'Enable variants on the product before adding variant records.',
            ]);
        }
    }

    private function assertVariantBelongs(Product $product, ProductVariant $variant): void
    {
        abort_unless((int) $variant->product_id === (int) $product->id, 404);
    }
}
