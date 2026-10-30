@php
    $isEdit = $product !== null;
    $attributes = old('attributes', $product?->attributes ?? []);
    $schema = $config->customAttributesEnabled() ? $config->customAttributeSchema() : [];
@endphp

@if ($errors->any())
    <div class="col-12">
        <div class="alert alert-danger mb-0">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

<div class="col-md-6">
    <label class="form-label">Name</label>
    <input type="text" name="name" class="form-control" required
           value="{{ old('name', $product?->name) }}">
</div>

<div class="col-md-3">
    <label class="form-label">SKU / product code</label>
    <input type="text" name="sku" class="form-control" required
           value="{{ old('sku', $product?->sku) }}">
</div>

<div class="col-md-3">
    <label class="form-label">Barcode</label>
    <input type="text" name="barcode" class="form-control"
           value="{{ old('barcode', $product?->barcode) }}">
</div>

<div class="col-md-4">
    <label class="form-label">Category</label>
    <select name="category_id" class="form-select">
        <option value="">Uncategorised</option>
        @foreach ($categories as $category)
            <option value="{{ $category->id }}"
                @selected((int) old('category_id', $product?->category_id) === $category->id)>
                {{ $category->name }}
            </option>
        @endforeach
    </select>
</div>

<div class="col-md-4">
    <label class="form-label">Unit of measurement</label>
    <select name="unit_of_measurement_id" class="form-select">
        <option value="">None</option>
        @foreach ($units as $unit)
            <option value="{{ $unit->id }}"
                @selected((int) old('unit_of_measurement_id', $product?->unit_of_measurement_id) === $unit->id)>
                {{ $unit->name }} ({{ $unit->code }})
            </option>
        @endforeach
    </select>
</div>

<div class="col-md-4">
    <label class="form-label">Product type</label>
    <select name="type" class="form-select">
        @foreach (['physical', 'composite', 'ingredient'] as $type)
            <option value="{{ $type }}" @selected(old('type', $product?->type ?? 'physical') === $type)>
                {{ ucfirst($type) }}
            </option>
        @endforeach
    </select>
</div>

<div class="col-md-3">
    <label class="form-label">Purchase cost</label>
    <input type="number" step="0.01" min="0" name="purchase_cost" class="form-control" required
           value="{{ old('purchase_cost', $product?->purchase_cost ?? '0.00') }}">
</div>

<div class="col-md-3">
    <label class="form-label">Selling price</label>
    <input type="number" step="0.01" min="0" name="selling_price" class="form-control" required
           value="{{ old('selling_price', $product?->selling_price ?? '0.00') }}">
</div>

<div class="col-md-2">
    <label class="form-label">Min stock level</label>
    <input type="number" min="0" name="min_stock_level" class="form-control"
           value="{{ old('min_stock_level', $product?->min_stock_level ?? 0) }}">
</div>

<div class="col-md-2">
    <label class="form-label">Max stock level</label>
    <input type="number" min="0" name="max_stock_level" class="form-control"
           value="{{ old('max_stock_level', $product?->max_stock_level) }}">
</div>

<div class="col-12">
    <label class="form-label">Description</label>
    <textarea name="description" rows="3" class="form-control">{{ old('description', $product?->description) }}</textarea>
</div>

<div class="col-md-4">
    <label class="form-label">Product image</label>
    <input type="file" name="image" accept="image/*" class="form-control">
    @if ($isEdit && ! empty($product->images))
        <div class="d-flex gap-2 mt-2">
            @foreach ($product->images as $image)
                <img src="{{ Storage::disk('public')->url($image) }}" alt="" class="rounded border" style="height:48px">
                <input type="hidden" name="images[]" value="{{ $image }}">
            @endforeach
        </div>
    @endif
</div>

<div class="col-md-8">
    <div class="form-check form-switch">
        <input type="hidden" name="track_inventory" value="0">
        <input class="form-check-input" type="checkbox" name="track_inventory" value="1"
               id="track_inventory" @checked(old('track_inventory', $product?->track_inventory ?? true))>
        <label class="form-check-label" for="track_inventory">Track inventory for this product</label>
    </div>
    <div class="form-check form-switch">
        <input type="hidden" name="allow_backorder" value="0">
        <input class="form-check-input" type="checkbox" name="allow_backorder" value="1"
               id="allow_backorder" @checked(old('allow_backorder', $product?->allow_backorder ?? false))>
        <label class="form-check-label" for="allow_backorder">Allow backorders when stock is exhausted</label>
    </div>
    <div class="form-check form-switch">
        @if ($config->variantsEnabled())
            <input type="hidden" name="has_variants" value="0">
        @endif
        <input class="form-check-input" type="checkbox" name="has_variants" value="1"
               id="has_variants" @checked(old('has_variants', $product?->has_variants ?? false))
               @disabled(! $config->variantsEnabled())>
        <label class="form-check-label" for="has_variants">
            This product has variants
            @unless ($config->variantsEnabled())
                <span class="text-muted small">(disabled for this business)</span>
            @endunless
        </label>
    </div>
    <div class="form-check form-switch">
        <input type="hidden" name="is_active" value="0">
        <input class="form-check-input" type="checkbox" name="is_active" value="1"
               id="is_active" @checked(old('is_active', $product?->is_active ?? true))>
        <label class="form-check-label" for="is_active">Active</label>
    </div>
</div>

@if ($schema !== [])
    <div class="col-12">
        <hr>
        <h6>Custom attributes</h6>
        <div class="row g-3">
            @foreach ($schema as $field)
                @php
                    $key = $field['key'] ?? null;
                    $label = $field['label'] ?? $key;
                    $type = $field['type'] ?? 'string';
                    $value = $attributes[$key] ?? null;
                @endphp
                @continue(! $key)
                <div class="col-md-4">
                    <label class="form-label">{{ $label }}</label>
                    @if ($type === 'select')
                        <select name="attributes[{{ $key }}]" class="form-select">
                            <option value="">—</option>
                            @foreach ($field['options'] ?? [] as $option)
                                <option value="{{ $option }}" @selected($value === $option)>{{ $option }}</option>
                            @endforeach
                        </select>
                    @elseif ($type === 'boolean')
                        <select name="attributes[{{ $key }}]" class="form-select">
                            <option value="">—</option>
                            <option value="1" @selected($value === true || $value === 1 || $value === '1')>Yes</option>
                            <option value="0" @selected($value === false || $value === 0 || $value === '0')>No</option>
                        </select>
                    @else
                        <input type="{{ $type === 'number' ? 'number' : ($type === 'date' ? 'date' : 'text') }}"
                               name="attributes[{{ $key }}]" class="form-control" value="{{ $value }}">
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@elseif ($config->customAttributesEnabled())
    <div class="col-12">
        <div class="alert alert-info mb-0">
            Custom attributes are enabled but no attribute schema is configured yet.
        </div>
    </div>
@endif
