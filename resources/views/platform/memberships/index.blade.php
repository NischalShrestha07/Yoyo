@extends('layouts.platform')

@section('platform-content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Members - {{ $business->name }}</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('platform.memberships.create', $business) }}" class="btn btn-sm btn-primary">Invite Member</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Roles</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($memberships as $membership)
                    <tr>
                        <td>{{ $membership->user->name }}</td>
                        <td>{{ $membership->user->email }}</td>
                        <td>{{ $membership->user->phone }}</td>
                        <td>
                            <span class="badge bg-{{ $membership->status === 'active' ? 'success' : ($membership->status === 'invited' ? 'warning' : 'secondary') }}">
                                {{ $membership->status }}
                            </span>
                        </td>
                        <td>
                            @forelse ($membership->roles as $role)
                                <span class="badge bg-primary">{{ $role->name }}</span>
                            @empty
                                <span class="text-muted">No roles</span>
                            @endforelse
                        </td>
                        <td>
                            @if($membership->joined_at)
                                {{ $membership->joined_at->format('M d, Y') }}
                            @else
                                <span class="text-muted">Not joined</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('platform.memberships.edit', ['business' => $business, 'membership' => $membership]) }}" class="btn btn-sm btn-info">Edit</a>
                            <form action="{{ route('platform.memberships.destroy', ['business' => $business, 'membership' => $membership]) }}" method="POST" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Remove</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">No members found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $memberships->links() }}
    </div>
</div>
@endsection