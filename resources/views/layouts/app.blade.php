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
            --primary: #1e40af;
            --sidebar-bg: #fff;
            --sidebar-border: #e5e7eb;
            --sidebar-hover: #f8fafc;
            --sidebar-active: #eff6ff;
            --sidebar-active-text: #1e40af;
        }
        body { background-color: #f8f9fa; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; font-size: 0.875rem; }
        .main-header { height: 64px; background: #fff; border-bottom: 1px solid var(--sidebar-border); display: flex; align-items: center; padding: 0 1.5rem; position: sticky; top: 0; z-index: 1000; }
        .header-left { display: flex; align-items: center; gap: 1rem; min-width: 260px; }
        .logo { display: flex; align-items: center; gap: 0.625rem; text-decoration: none; color: #111827; font-weight: 600; font-size: 1.125rem; }
        .logo-icon { width: 36px; height: 36px; background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #fff; }
        .header-search { flex: 1; max-width: 400px; }
        .header-search .form-control { border-radius: 8px; border: 1px solid #d1d5db; font-size: 0.875rem; }
        .sidebar { width: 260px; background: var(--sidebar-bg); border-right: 1px solid var(--sidebar-border); height: calc(100vh - 64px); position: fixed; left: 0; top: 64px; overflow-y: auto; overflow-x: hidden; }
        .sidebar-menu { padding: 1rem 0.75rem; }
        .menu-title { color: #6b7280; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; padding: 0.75rem 0.75rem 0.375rem; margin-top: 0.5rem; }
        .menu-item { list-style: none; margin-bottom: 2px; }
        .menu-link { display: flex; align-items: center; gap: 0.75rem; padding: 0.5rem 0.75rem; color: #374151; text-decoration: none; border-radius: 8px; font-size: 0.875rem; transition: all 0.15s; }
        .menu-link:hover { background-color: var(--sidebar-hover); color: #111827; }
        .menu-link.active { background-color: var(--sidebar-active); color: var(--sidebar-active-text); font-weight: 500; }
        .menu-link i { font-size: 1.125rem; width: 20px; text-align: center; }
        .main-content-wrapper { margin-left: 260px; min-height: calc(100vh - 64px); background: #f8f9fa; }
        .page-content { padding: 1.5rem; }
        .card { box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); border: 1px solid #e5e7eb; border-radius: 10px; }
        .card-header { background-color: #fff; border-bottom: 1px solid #e5e7eb; font-weight: 600; padding: 1rem 1.25rem; border-radius: 10px 10px 0 0 !important; }
        .btn { border-radius: 8px; font-weight: 500; font-size: 0.875rem; padding: 0.5rem 0.875rem; }
        .btn-primary { background-color: #2563eb; border-color: #2563eb; }
        .btn-primary:hover { background-color: #1d4ed8; border-color: #1d4ed8; }
        .table th { font-weight: 600; color: #374151; background-color: #f9fafb; font-size: 0.8125rem; text-transform: uppercase; letter-spacing: 0.025em; }
        .form-control, .form-select { border-radius: 8px; border: 1px solid #d1d5db; font-size: 0.875rem; }
        .dropdown-menu { border-radius: 10px; border: 1px solid #e5e7eb; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
    </style>
    @stack('styles')
</head>
<body>
    @auth
        <header class="main-header">
            <div class="header-left">
                <a href="{{ route('dashboard') }}" class="logo">
                    <div class="logo-icon">
                        <i class="bi bi-shop"></i>
                    </div>
                    <span>{{ config('app.name', 'Retail ERP') }}</span>
                </a>
            </div>
            <div class="header-search d-none d-md-flex">
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" class="form-control border-start-0 ps-0" placeholder="Search...">
                </div>
            </div>
            <div class="ms-auto d-flex align-items-center gap-3">
                @isset($business)
                    <a href="{{ route('business.pos.index', $business) }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-cart-check"></i> POS
                    </a>
                @endisset
                <div class="dropdown">
                    <a class="d-flex align-items-center gap-2 text-decoration-none text-dark" href="#" role="button" data-bs-toggle="dropdown">
                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                            <i class="bi bi-person-fill"></i>
                        </div>
                        <div class="d-none d-md-block">
                            <div class="fw-medium" style="font-size: 0.875rem;">{{ Auth::user()->name }}</div>
                            <div class="text-muted" style="font-size: 0.75rem;">{{ Auth::user()->email }}</div>
                        </div>
                        <i class="bi bi-chevron-down text-muted"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('dashboard') }}"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i> Logout</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <aside class="sidebar">
            <nav class="sidebar-menu">
                <ul class="list-unstyled mb-0">
                    <li class="menu-item">
                        <a class="menu-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                            <i class="bi bi-speedometer2"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    @isset($business)
                        <li class="menu-title">Sales</li>
                        <li class="menu-item">
                            <a class="menu-link {{ request()->routeIs('business.pos.*') ? 'active' : '' }}" href="{{ route('business.pos.index', $business) }}">
                                <i class="bi bi-cart-check"></i>
                                <span>Point of Sale</span>
                            </a>
                        </li>
                        <li class="menu-item">
                            <a class="menu-link {{ request()->routeIs('business.sales.*') ? 'active' : '' }}" href="{{ route('business.sales.index', $business) }}">
                                <i class="bi bi-receipt"></i>
                                <span>Sales</span>
                            </a>
                        </li>
                        <li class="menu-item">
                            <a class="menu-link {{ request()->routeIs('business.quotations.*') ? 'active' : '' }}" href="{{ route('business.quotations.index', $business) }}">
                                <i class="bi bi-file-earmark-text"></i>
                                <span>Quotations</span>
                            </a>
                        </li>
                        <li class="menu-item">
                            <a class="menu-link {{ request()->routeIs('business.customers.*') ? 'active' : '' }}" href="{{ route('business.customers.index', $business) }}">
                                <i class="bi bi-people"></i>
                                <span>Customers</span>
                            </a>
                        </li>

                        <li class="menu-title">Purchases</li>
                        <li class="menu-item">
                            <a class="menu-link {{ request()->routeIs('business.purchases.*') ? 'active' : '' }}" href="{{ route('business.purchases.index', $business) }}">
                                <i class="bi bi-bag-check"></i>
                                <span>Purchase Orders</span>
                            </a>
                        </li>
                        <li class="menu-item">
                            <a class="menu-link {{ request()->routeIs('business.suppliers.*') ? 'active' : '' }}" href="{{ route('business.suppliers.index', $business) }}">
                                <i class="bi bi-truck"></i>
                                <span>Suppliers</span>
                            </a>
                        </li>

                        <li class="menu-title">Inventory</li>
                        <li class="menu-item">
                            <a class="menu-link {{ request()->routeIs('business.products.*') ? 'active' : '' }}" href="{{ route('business.products.index', $business) }}">
                                <i class="bi bi-box"></i>
                                <span>Products</span>
                            </a>
                        </li>
                        <li class="menu-item">
                            <a class="menu-link {{ request()->routeIs('business.categories.*') ? 'active' : '' }}" href="{{ route('business.categories.index', $business) }}">
                                <i class="bi bi-tags"></i>
                                <span>Categories</span>
                            </a>
                        </li>
                        <li class="menu-item">
                            <a class="menu-link {{ request()->routeIs('business.inventory.*') ? 'active' : '' }}" href="{{ route('business.inventory.index', $business) }}">
                                <i class="bi bi-boxes"></i>
                                <span>Stock</span>
                            </a>
                        </li>
                        <li class="menu-item">
                            <a class="menu-link {{ request()->routeIs('business.stock-locations.*') ? 'active' : '' }}" href="{{ route('business.stock-locations.index', $business) }}">
                                <i class="bi bi-geo-alt"></i>
                                <span>Locations</span>
                            </a>
                        </li>

                        <li class="menu-title">Finance</li>
                        <li class="menu-item">
                            <a class="menu-link {{ request()->routeIs('business.expenses.*') ? 'active' : '' }}" href="{{ route('business.expenses.index', $business) }}">
                                <i class="bi bi-cash-stack"></i>
                                <span>Expenses</span>
                            </a>
                        </li>
                        <li class="menu-item">
                            <a class="menu-link {{ request()->routeIs('business.cash-registers.*') ? 'active' : '' }}" href="{{ route('business.cash-registers.index', $business) }}">
                                <i class="bi bi-cash"></i>
                                <span>Cash Register</span>
                            </a>
                        </li>

                        <li class="menu-title">Reports & Settings</li>
                        <li class="menu-item">
                            <a class="menu-link {{ request()->routeIs('business.reports.*') ? 'active' : '' }}" href="{{ route('business.reports.index', $business) }}">
                                <i class="bi bi-bar-chart-line"></i>
                                <span>Reports</span>
                            </a>
                        </li>
                        <li class="menu-item">
                            <a class="menu-link {{ request()->routeIs('business.branches.*') ? 'active' : '' }}" href="{{ route('business.branches.index', $business) }}">
                                <i class="bi bi-diagram-3"></i>
                                <span>Branches</span>
                            </a>
                        </li>
                        <li class="menu-item">
                            <a class="menu-link {{ request()->routeIs('business.settings.*') ? 'active' : '' }}" href="{{ route('business.settings.index', $business) }}">
                                <i class="bi bi-gear"></i>
                                <span>Settings</span>
                            </a>
                        </li>
                    @endisset
                </ul>
            </nav>
        </aside>

        <div class="main-content-wrapper">
            <main class="page-content">
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
    @else
        <main class="container py-5">
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
            @yield('content')
        </main>
    @endauth

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
