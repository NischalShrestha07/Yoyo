@if ($errors->any())
    <div class="col-12">
        <div class="alert alert-danger mb-0">
            <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    </div>
@endif

<div class="col-md-5">
    <label class="form-label">Name</label>
    <input type="text" name="name" class="form-control" required value="{{ old('name', $unit?->name) }}">
</div>
<div class="col-md-3">
    <label class="form-label">Code</label>
    <input type="text" name="code" class="form-control" required value="{{ old('code', $unit?->code) }}"
           placeholder="PCS">
</div>
<div class="col-md-4">
    <label class="form-label">Description</label>
    <input type="text" name="description" class="form-control" value="{{ old('description', $unit?->description) }}">
</div>
<div class="col-md-3 d-flex align-items-end">
    <div class="form-check form-switch">
        <input type="hidden" name="is_active" value="0">
        <input class="form-check-input" type="checkbox" name="is_active" value="1"
               id="unit_is_active" @checked(old('is_active', $unit?->is_active ?? true))>
        <label class="form-check-label" for="unit_is_active">Active</label>
    </div>
</div>
