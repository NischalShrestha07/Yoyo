@extends('layouts.platform')

@section('platform-content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Create Business</h1>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('platform.businesses.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="name" class="form-label">Business Name</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required autofocus>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="type" class="form-label">Business Type</label>
                        <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                            <option value="">Select business type</option>
                            <option value="general" {{ old('type') == 'general' ? 'selected' : '' }}>General</option>
                            <option value="grocery" {{ old('type') == 'grocery' ? 'selected' : '' }}>Grocery</option>
                            <option value="electronics" {{ old('type') == 'electronics' ? 'selected' : '' }}>Electronics</option>
                            <option value="repair" {{ old('type') == 'repair' ? 'selected' : '' }}>Repair Center</option>
                            <option value="clothing" {{ old('type') == 'clothing' ? 'selected' : '' }}>Clothing</option>
                            <option value="fastfood" {{ old('type') == 'fastfood' ? 'selected' : '' }}>Fast Food</option>
                            <option value="custom" {{ old('type') == 'custom' ? 'selected' : '' }}>Custom</option>
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="3">{{ old('address') }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="tax_id" class="form-label">Tax ID</label>
                        <input type="text" class="form-control @error('tax_id') is-invalid @enderror" id="tax_id" name="tax_id" value="{{ old('tax_id') }}">
                        @error('tax_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="is_active" name="is_active" {{ old('is_active') ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Activate business immediately</label>
                    </div>

                    <button type="submit" class="btn btn-primary">Create Business</button>
                    <a href="{{ route('platform.businesses.index') }}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Business Types</h5>
                <ul class="list-unstyled">
                    <li><strong>General:</strong> Standard retail business</li>
                    <li><strong>Grocery:</strong> Food and grocery store</li>
                    <li><strong>Electronics:</strong> Electronics and gadgets</li>
                    <li><strong>Repair:</strong> Repair service center</li>
                    <li><strong>Clothing:</strong> Clothing and apparel store</li>
                    <li><strong>Fast Food:</strong> Quick service restaurant</li>
                    <li><strong>Custom:</strong> Custom business type</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection