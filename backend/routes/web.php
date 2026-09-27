<?php

use App\Http\Controllers\Admin\AdController;
use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\AuthPanelSettingController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ExportController;
use App\Http\Controllers\Admin\ModelController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SubcategoryController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])
        ->middleware('throttle:10,1')
        ->name('login');

    Route::post('login', [AuthController::class, 'login'])
        ->middleware('throttle:6,1');

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');

        Route::get('', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('analytics', [DashboardController::class, 'analytics'])->name('analytics');

        Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
        Route::post('notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
        Route::get('notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');

        // Products
        Route::get('products', [ProductController::class, 'index'])->name('products.index');
        Route::get('products/{product}', [ProductController::class, 'show'])->name('products.show');
        Route::post('products/{product}/toggle-active', [ProductController::class, 'toggleActive'])->name('products.toggle-active');
        Route::post('products/{product}/approve', [ProductController::class, 'approve'])->name('products.approve');
        Route::post('products/{product}/reject', [ProductController::class, 'reject'])->name('products.reject');
        Route::delete('products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

        // Users
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');
        Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::post('users/{user}/toggle-admin', [UserController::class, 'toggleAdmin'])->name('users.toggle-admin');

        // Reports
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::post('reports/{report}/resolve', [ReportController::class, 'resolve'])->name('reports.resolve');

        // Ads
        Route::get('ads', [AdController::class, 'index'])->name('ads.index');
        Route::get('ads/create', [AdController::class, 'create'])->name('ads.create');
        Route::post('ads', [AdController::class, 'store'])->name('ads.store');
        Route::get('ads/{ad}/edit', [AdController::class, 'edit'])->name('ads.edit');
        Route::put('ads/{ad}', [AdController::class, 'update'])->name('ads.update');
        Route::delete('ads/{ad}', [AdController::class, 'destroy'])->name('ads.destroy');
        Route::post('ads/{ad}/toggle-active', [AdController::class, 'toggleActive'])->name('ads.toggle-active');

        // Exports
        Route::get('exports/{resource}', [ExportController::class, 'index'])
            ->whereIn('resource', ['products', 'reports', 'users', 'categories', 'subcategories', 'brands', 'models', 'attributes'])
            ->name('exports.index');

        // Settings
        Route::get('settings/auth-panel', [AuthPanelSettingController::class, 'edit'])->name('settings.auth-panel');
        Route::put('settings/auth-panel', [AuthPanelSettingController::class, 'update'])->name('settings.auth-panel.update');

        // Catalog
        Route::resource('categories', CategoryController::class)->except(['show']);
        Route::post('categories/{category}/toggle-active', [CategoryController::class, 'toggleActive'])->name('categories.toggle-active');

        Route::resource('subcategories', SubcategoryController::class)->except(['show']);
        Route::post('subcategories/{subcategory}/toggle-active', [SubcategoryController::class, 'toggleActive'])->name('subcategories.toggle-active');

        Route::resource('brands', BrandController::class)->except(['show']);
        Route::resource('models', ModelController::class)->except(['show']);
        Route::resource('attributes', AttributeController::class)->except(['show']);
    });
});
