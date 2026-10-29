<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Business::class);

        $businesses = Business::withCount('memberships', 'modules')
            ->latest()
            ->paginate(10);

        $totalUsers = User::count();
        $totalBusinesses = Business::count();
        $activeBusinesses = Business::where('is_active', true)->count();

        return view('platform.dashboard.index', compact(
            'businesses',
            'totalUsers',
            'totalBusinesses',
            'activeBusinesses'
        ));
    }
}