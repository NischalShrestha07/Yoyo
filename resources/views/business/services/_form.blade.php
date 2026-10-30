@php
    $isEdit = $service !== null;
@endphp

@if ($errors->any())
    <div class="col-12">
        <div class="alert alert-danger mb-0">
            <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    </div>
@endif

<div class="col-md-6">
    <label class="form-label">Name</label>
    <input type="text" name="name" class="form-control" required value="{{ old('name', $service?->name) }}">
</div>
<div class="col-md-3">
    <label class="form-label">Service code</label>
    <input type="text" name="code" class="form-control" required value="{{ old('code', $service?->code) }}">
</div>
<div class="col-md-3">
    <label class="form-label">Category</label>
    <select name="category_id" class="form-select">
        <option value="">Uncategorised</option>
        @foreach ($categories as $category)
            <option value="{{ $category->id }}"
                @selected((int) old('category_id', $service?->category_id) === $category->id)>
                {{ $category->name }}
            </option>
        @endforeach
    </select>
</div>
<div class="col-md-3">
    <label class="form-label">Selling price</label>
    <input type="number" step="0.01" min="0" name="selling_price" class="form-control" required
           value="{{ old('selling_price', $service?->selling_price ?? '0.00') }}">
</div>
<div class="col-md-3 d-flex align-items-end">
    <div class="form-check form-switch">
        <input type="hidden" name="is_active" value="0">
        <input class="form-check-input" type="checkbox" name="is_active" value="1"
               id="service_is_active" @checked(old('is_active', $service?->is_active ?? true))>
        <label class="form-check-label" for="service_is_active">Active</label>
    </div>
</div>
<div class="col-12">
    <label class="form-label">Description</label>
    <textarea name="description" rows="3" class="form-control">{{ old('description', $service?->description) }}</textarea>
</div>
