@extends('layouts.platform')

@section('platform-content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Edit Member - {{ $membership->user->name }}</h1>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('platform.memberships.update', ['business' => $business, 'membership' => $membership]) }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                            <option value="invited" {{ old('status', $membership->status) == 'invited' ? 'selected' : '' }}>Invited</option>
                            <option value="active" {{ old('status', $membership->status) == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="suspended" {{ old('status', $membership->status) == 'suspended' ? 'selected' : '' }}>Suspended</option>
                            <option value="inactive" {{ old('status', $membership->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="roles" class="form-label">Roles</label>
                        <select class="form-select @error('roles') is-invalid @enderror" id="roles" name="roles[]" multiple>
                            @foreach($business->roles as $role)
                                <option value="{{ $role->id }}" {{ old('roles', $membership->roles->pluck('id')->toArray()) && in_array($role->id, old('roles', $membership->roles->pluck('id')->toArray())) ? 'selected' : '' }}>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Hold Ctrl/Cmd to select multiple roles</small>
                        @error('roles')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary">Update Member</button>
                    <a href="{{ route('platform.memberships.index', $business) }}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Member Information</h5>
                <p><strong>Name:</strong> {{ $membership->user->name }}</p>
                <p><strong>Email:</strong> {{ $membership->user->email }}</p>
                <p><strong>Phone:</strong> {{ $membership->user->phone }}</p>
                <p><strong>Status:</strong> 
                    @if($membership->status === 'active')
                        <span class="badge bg-success">Active</span>
                    @elseif($membership->status === 'invited')
                        <span class="badge bg-warning">Invited</span>
                    @elseif($membership->status === 'suspended')
                        <span class="badge bg-warning">Suspended</span>
                    @else
                        <span class="badge bg-secondary">Inactive</span>
                    @endif
                </p>
                <p><strong>Joined:</strong> 
                    @if($membership->joined_at)
                        {{ $membership->joined_at->format('M d, Y') }}
                    @else
                        <span class="text-muted">Not joined</span>
                    @endif
                </p>
            </div>
        </div>
    </div>
</div>
@endsection