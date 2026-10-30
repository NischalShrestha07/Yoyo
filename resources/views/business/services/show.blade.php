@extends('layouts.app')

@section('title', $service->name)

@section('content')
    @include('business._nav', ['business' => $business])

    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h5 class="mb-1">{{ $service->name }}</h5>
            <div class="text-muted">
                <code>{{ $service->code }}</code>
                · {{ $service->category?->name ?? 'Uncategorised' }}
                · <span class="badge bg-{{ $service->is_active ? 'success' : 'secondary' }}">
                    {{ $service->is_active ? 'Active' : 'Archived' }}
                </span>
            </div>
        </div>
        <a href="{{ route('business.services.edit', [$business, $service]) }}" class="btn btn-outline-secondary btn-sm">Edit</a>
    </div>

    <div class="alert alert-info">
        Services do not carry physical stock and never create stock movements.
    </div>

    <dl class="row">
        <dt class="col-sm-3">Selling price</dt>
        <dd class="col-sm-9">{{ number_format((float) $service->selling_price, 2) }}</dd>
        <dt class="col-sm-3">Description</dt>
        <dd class="col-sm-9">{{ $service->description ?: '—' }}</dd>
    </dl>
@endsection
