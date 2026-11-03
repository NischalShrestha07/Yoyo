<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Retail ERP') }} - @yield('title', 'ERP')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --sidebar-bg: #0f172a;
            --sidebar-hover: #1e293b;
            --sidebar-active: #2563eb;
        }
        body { background-color: #f1f5f9; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
        .navbar { background: linear-gradient(90deg, #1e40af 0%, #2563eb 100%) !important; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .sidebar { min-height: calc(100vh - 56px); background: var(--sidebar-bg); border-right: none; }
        .sidebar .nav-link { color: #cbd5e1; border-radius: 0.5rem; margin: 4px 12px; padding: 0.625rem 0.875rem; display: flex; align-items: center; gap: 0.625rem; font-size: 0.9rem; transition: all 0.2s; }
        .sidebar .nav-link:hover { background-color: var(--sidebar-hover); color: #fff; }
        .sidebar .nav-link.active { background-color: var(--sidebar-active); color: #fff; font-weight: 500; }
        .sidebar .nav-section { color: #64748b; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; padding: 1rem 1.5rem 0.5rem; margin-top: 0.5rem; }
        .main-content { padding: 1.75rem; max-width: 1600px; }
        .card { box-shadow: 0 1px 3px rgba(0,0,0,0.05), 0 1px 2px rgba(0,0,0,0.03); border: 1px solid #e2e8f0; border-radius: 0.75rem; }
        .card-header { background-color: #fff; border-bottom: 1px solid #e2e8f0; font-weight: 600; padding: 1rem 1.25rem; border-radius: 0.75rem 0.75rem 0 0 !important; }
        .btn { border-radius: 0.5rem; font-weight: 500; }
        .table { font-size: 0.9rem; }
        .table th { font-weight: 600; color: #475569; background-color: #f8fafc; border-bottom: 2px solid #e2e8f0; }
        .badge { font-weight: 500; padding: 0.375rem 0.625rem; }
    </style>
    @stack('styles')
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="{{ route('dashboard') }}">
                <i class="bi bi-shop-window"></i> {{ config('app.name', 'Retail ERP') }}
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    @auth
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown">
                                <div class="rounded-circle bg-white text-primary d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                    <i class="bi bi-person-fill"></i>
                                </div>
                                <span>{{ Auth::user()->name }}</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                <li><a class="dropdown-item" href="{{ route('dashboard') }}"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i> Logout</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">Register</a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid p-0">
        <div class="row g-0">
            @auth
                <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                    <div class="py-3">
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                                    <i class="bi bi-speedometer2"></i> <span>Dashboard</span>
                                </a>
                            </li>
                            <li class="nav-section">Quick Access</li>
                            @isset($business)
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('business.pos.*') ? 'active' : '' }}" href="{{ route('business.pos.index', $business) }}">
                                        <i class="bi bi-cart-check"></i> <span>POS Terminal</span>
                                    </a>
                                </li>
                            @endisset
                        </ul>
                    </div>
                </nav>
                <main class="col-md-9 ms-sm-auto col-lg-10 main-content">
            @else
                <main class="col-12">
            @endauth
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle me-2"></i> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <div class="fw-semibold mb-1"><i class="bi bi-exclamation-circle me-2"></i>Please fix the following errors:</div>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    @yield('content')
                </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
