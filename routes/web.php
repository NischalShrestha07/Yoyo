<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Platform\BusinessController;
use App\Http\Controllers\Platform\DashboardController as PlatformDashboardController;
use App\Http\Controllers\Platform\MemberShipController;
use App\Http\Controllers\Platform\ModuleController;
use App\Http\Controllers\Platform\SettingController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

// Platform Administration Routes
Route::prefix('platform')->name('platform.')->middleware(['auth', 'active'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [PlatformDashboardController::class, 'index'])->name('dashboard');
    
    // Businesses
    Route::resource('businesses', BusinessController::class);
    
    // Memberships
    Route::prefix('businesses/{business}')->group(function () {
        Route::get('/memberships', [MemberShipController::class, 'index'])->name('memberships.index');
        Route::get('/memberships/create', [MemberShipController::class, 'create'])->name('memberships.create');
        Route::post('/memberships', [MemberShipController::class, 'store'])->name('memberships.store');
        Route::get('/memberships/{membership}/edit', [MemberShipController::class, 'edit'])->name('memberships.edit');
        Route::put('/memberships/{membership}', [MemberShipController::class, 'update'])->name('memberships.update');
        Route::delete('/memberships/{membership}', [MemberShipController::class, 'destroy'])->name('memberships.destroy');
        
        // Modules
        Route::get('/modules/edit', [ModuleController::class, 'edit'])->name('modules.edit');
        Route::put('/modules', [ModuleController::class, 'update'])->name('modules.update');
        
        // Settings
        Route::get('/settings', [SettingController::class, 'businessEdit'])->name('settings.business');
        Route::put('/settings', [SettingController::class, 'businessUpdate'])->name('settings.update');
    });
});

// Platform Settings (global)
Route::prefix('platform')->name('platform.')->middleware(['auth', 'active'])->group(function () {
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::get('/settings/edit', [SettingController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
});