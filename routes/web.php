<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Platform\BusinessController;
use App\Http\Controllers\Platform\DashboardController as PlatformDashboardController;
use App\Http\Controllers\Platform\MembershipController;
use App\Http\Controllers\Platform\ModuleController;
use App\Http\Controllers\Platform\SettingController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductVariantController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\UnitOfMeasurementController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\StockLocationController;
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
    
    // Business routes
    Route::prefix('businesses/{business}')->name('business.')->group(function () {
        // Products
        Route::resource('products', ProductController::class);
        Route::post('products/{product}/archive', [ProductController::class, 'archive'])->name('products.archive');
        Route::resource('products.variants', ProductVariantController::class)->except(['show']);
        
        // Services
        Route::resource('services', ServiceController::class);
        Route::post('services/{service}/archive', [ServiceController::class, 'archive'])->name('services.archive');
        
        // Categories
        Route::resource('categories', CategoryController::class);
        
        // Units of measurement
        Route::resource('units', UnitOfMeasurementController::class);

        // Stock Locations
        Route::resource('stock-locations', StockLocationController::class);
        
        // Inventory
        Route::get('inventory', [StockController::class, 'index'])->name('inventory.index');
        Route::get('inventory/receipt', [StockController::class, 'receiptForm'])->name('inventory.receipt.form');
        Route::post('inventory/receipt', [StockController::class, 'receipt'])->name('inventory.receipt');
        Route::get('inventory/adjustment', [StockController::class, 'adjustmentForm'])->name('inventory.adjustment.form');
        Route::post('inventory/adjustment', [StockController::class, 'adjustment'])->name('inventory.adjustment');
        Route::get('inventory/transfer', [StockController::class, 'transferForm'])->name('inventory.transfer.form');
        Route::post('inventory/transfer', [StockController::class, 'transfer'])->name('inventory.transfer');
        Route::get('inventory/return', [StockController::class, 'returnForm'])->name('inventory.return.form');
        Route::post('inventory/return', [StockController::class, 'returnStock'])->name('inventory.return');
        Route::get('inventory/movements', [StockController::class, 'movements'])->name('inventory.movements');
        Route::post('inventory/movements/{movement}/reverse', [StockController::class, 'reverse'])->name('inventory.movements.reverse');
        Route::post('inventory/reservations', [StockController::class, 'reserve'])->name('inventory.reservations.store');
        Route::delete('inventory/reservations/{reservation}', [StockController::class, 'releaseReservation'])->name('inventory.reservations.destroy');
        Route::get('inventory/settings', [StockController::class, 'settingsForm'])->name('inventory.settings');
        Route::post('inventory/settings', [StockController::class, 'settingsUpdate'])->name('inventory.settings.update');
    });
});

// Platform Administration Routes
Route::prefix('platform')->name('platform.')->middleware(['auth', 'active'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [PlatformDashboardController::class, 'index'])->name('dashboard');
    
    // Businesses
    Route::resource('businesses', BusinessController::class);
    
    // Memberships
    Route::prefix('businesses/{business}')->group(function () {
        Route::get('/memberships', [MembershipController::class, 'index'])->name('memberships.index');
        Route::get('/memberships/create', [MembershipController::class, 'create'])->name('memberships.create');
        Route::post('/memberships', [MembershipController::class, 'store'])->name('memberships.store');
        Route::get('/memberships/{membership}/edit', [MembershipController::class, 'edit'])->name('memberships.edit');
        Route::put('/memberships/{membership}', [MembershipController::class, 'update'])->name('memberships.update');
        Route::delete('/memberships/{membership}', [MembershipController::class, 'destroy'])->name('memberships.destroy');
        
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