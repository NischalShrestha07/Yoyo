@extends('layouts.platform')

@section('platform-content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Platform Settings</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('platform.settings.edit') }}" class="btn btn-sm btn-primary">Edit Settings</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Setting Key</th>
                        <th>Type</th>
                        <th>Value</th>
                        <th>Created</th>
                        <th>Updated</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($settings as $setting)
                    <tr>
                        <td>{{ $setting->key }}</td>
                        <td>
                            <span class="badge bg-secondary">{{ $setting->type }}</span>
                        </td>
                        <td>
                            @if($setting->is_encrypted)
                                <span class="text-muted">[Encrypted]</span>
                            @else
                                {{ $setting->value }}
                            @endif
                        </td>
                        <td>{{ $setting->created_at->format('M d, Y') }}</td>
                        <td>{{ $setting->updated_at->format('M d, Y') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center">No settings found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $settings->links() }}
    </div>
</div>
@endsection