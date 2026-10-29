@extends('layouts.platform')

@section('platform-content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Manage Modules - {{ $business->name }}</h1>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('platform.modules.update', $business) }}" method="POST">
            @csrf

            <div class="mb-4">
                <h5>Available Modules</h5>
                <p class="text-muted">Select which modules to enable for this business. Some modules may have dependencies that must also be enabled.</p>
            </div>

            <div class="row">
                @foreach($modules as $module)
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" 
                                           name="modules[{{ $module->id }}][enabled]" 
                                           id="module-{{ $module->id }}"
                                           value="1"
                                           {{ $businessModules->where('module_id', $module->id)->first()?->enabled ? 'checked' : '' }}>
                                    <label class="form-check-label" for="module-{{ $module->id }}">
                                        <strong>{{ $module->name }}</strong>
                                    </label>
                                </div>
                                <p class="text-muted small">{{ $module->description }}</p>
                                @if($module->is_core)
                                    <span class="badge bg-primary">Core Module</span>
                                @endif
                                @if($module->dependencies)
                                    <div class="mt-2">
                                        <small class="text-muted">Dependencies:
                                            @foreach($module->dependencies as $dependency)
                                                <span class="badge bg-secondary">{{ $dependency }}</span>
                                            @endforeach
                                        </small>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <button type="submit" class="btn btn-primary">Save Module Settings</button>
            <a href="{{ route('platform.businesses.show', $business) }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
@endsection