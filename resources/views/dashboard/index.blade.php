@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Dashboard</h1>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Welcome, {{ Auth::user()->name }}!</h5>
                <p class="card-text">You are logged in to the Retail ERP system.</p>
            </div>
        </div>
    </div>
</div>
@endsection
