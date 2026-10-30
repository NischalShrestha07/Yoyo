@if ($errors->any())
    <div class="col-12">
        <div class="alert alert-danger mb-0">
            <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    </div>
@endif

<div class="col-md-6">
    <label class="form-label">Name</label>
    <input type="text" name="name" class="form-control" required value="{{ old('name', $category?->name) }}">
</div>
<div class="col-md-4">
    <label class="form-label">Parent category</label>
    <select name="parent_id" class="form-select">
        <option value="">Top level</option>
        @foreach ($parents as $parent)
            <option value="{{ $parent->id }}"
                @selected((int) old('parent_id', $category?->parent_id) === $parent->id)>
                {{ $parent->name }}
            </option>
        @endforeach
    </select>
</div>
<div class="col-md-2">
    <label class="form-label">Sort order</label>
    <input type="number" min="0" name="sort_order" class="form-control"
           value="{{ old('sort_order', $category?->sort_order ?? 0) }}">
</div>
<div class="col-12">
    <label class="form-label">Description</label>
    <textarea name="description" rows="3" class="form-control">{{ old('description', $category?->description) }}</textarea>
</div>
<div class="col-md-3 d-flex align-items-end">
    <div class="form-check form-switch">
        <input type="hidden" name="is_active" value="0">
        <input class="form-check-input" type="checkbox" name="is_active" value="1"
               id="category_is_active" @checked(old('is_active', $category?->is_active ?? true))>
        <label class="form-check-label" for="category_is_active">Active</label>
    </div>
</div>
