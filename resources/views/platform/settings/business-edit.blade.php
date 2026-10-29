@extends('layouts.platform')

@section('platform-content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Business Settings - {{ $business->name }}</h1>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('platform.settings.update', $business) }}" method="POST">
            @csrf

            <div class="mb-4">
                <h5>Business Configuration</h5>
                <p class="text-muted">Configure settings specific to this business.</p>
            </div>

            <div class="row">
                @foreach($settings as $setting)
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <label for="settings-{{ $setting->key }}" class="form-label">{{ $setting->key }}</label>
                                @if($setting->type === 'boolean')
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" 
                                               id="settings-{{ $setting->key }}"
                                               name="settings[{{ $setting->key }}][value]"
                                               value="1"
                                               {{ old('settings.' . $setting->key . '.value', $setting->value) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="settings-{{ $setting->key }}">
                                            {{ $setting->key }}
                                        </label>
                                    </div>
                                @elseif($setting->type === 'array')
                                    <textarea class="form-control" 
                                              id="settings-{{ $setting->key }}"
                                              name="settings[{{ $setting->key }}][value]"
                                              rows="3">{{ old('settings.' . $setting->key . '.value', json_encode($setting->value, JSON_PRETTY_PRINT)) }}</textarea>
                                @else
                                    <input type="text" 
                                           class="form-control" 
                                           id="settings-{{ $setting->key }}"
                                           name="settings[{{ $setting->key }}][value]"
                                           value="{{ old('settings.' . $setting->key . '.value', $setting->value) }}">
                                @endif
                                
                                <input type="hidden" name="settings[{{ $setting->key }}][key]" value="{{ $setting->key }}">
                                
                                @if($setting->is_encrypted)
                                    <small class="text-muted">This setting is encrypted</small>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <button type="submit" class="btn btn-primary">Save Settings</button>
            <a href="{{ route('platform.businesses.show', $business) }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
@endsection