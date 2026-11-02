<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $businesses = $user->businesses()->get();
        return view('dashboard.index', compact('businesses'));
    }
}
