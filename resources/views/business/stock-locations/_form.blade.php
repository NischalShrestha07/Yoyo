@if ($errors->any())
    <div class="col-12">
        <div class="alert alert-danger mb-0">
            <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    </div>
@endif

<div class="col-md-5">
    <label class="form-label">Name</label>
    <input type="text" name="name" class="form-control" required value="{{ old('name', $stockLocation?->name) }}">
</div>
<div class="col-md-3">
    <label class="form-label">Code</label>
    <input type="text" name="code" class="form-control" required value="{{ old('code', $stockLocation?->code) }}">
</div>
<div class="col-md-4">
    <label class="form-label">Address</label>
    <input type="text" name="address" class="form-control" value="{{ old('address', $stockLocation?->address) }}">
</div>
<div class="col-md-3">
    <div class="form-check form-switch mt-4">
        <input type="hidden" name="is_default" value="0">
        <input class="form-check-input" type="checkbox" name="is_default" value="1"
               id="is_default" @checked(old('is_default', $stockLocation?->is_default ?? false))>
        <label class="form-check-label" for="is_default">Default location</label>
    </div>
</div>
<div class="col-md-3">
    <div class="form-check form-switch mt-4">
        <input type="hidden" name="is_active" value="0">
        <input class="form-check-input" type="checkbox" name="is_active" value="1"
               id="is_active" @checked(old('is_active', $stockLocation?->is_active ?? true))>
        <label class="form-check-label" for="is_active">Active</label>
    </div>
</div>
