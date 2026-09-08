<?php

use App\Http\Controllers\Admin\AttributeController as AdminAttributeController;
use App\Http\Controllers\Admin\BrandController as AdminBrandController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ModelController as AdminModelController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\SubcategoryController as AdminSubcategoryController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AttributeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\ModelController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductImageController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SubcategoryController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Search
Route::get('/search', SearchController::class);

// Auth
Route::post('/register', [AuthController::class, 'register']);
Route::post('/verify', [AuthController::class, 'verify']);
Route::post('/resend', [AuthController::class, 'resend']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/me', [AuthController::class, 'me'])->middleware('auth:sanctum');
Route::put('/profile', [AuthController::class, 'updateProfile'])->middleware('auth:sanctum');
Route::delete('/profile', [AuthController::class, 'deleteAccount'])->middleware('auth:sanctum');
Route::post('/profile/delete-otp', [AuthController::class, 'sendDeleteOtp'])->middleware('auth:sanctum');
Route::post('/avatar', [AuthController::class, 'uploadAvatar'])->middleware('auth:sanctum');
Route::post('/cover-image', [AuthController::class, 'uploadCoverImage'])->middleware('auth:sanctum');

// Categories
Route::get('/categories', [CategoryController::class, 'index']);
Route::post('/categories', [CategoryController::class, 'store'])->middleware('auth:sanctum');
Route::put('/categories/{category}', [CategoryController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->middleware('auth:sanctum');

// Subcategories
Route::get('/categories/{category}/subcategories', [SubcategoryController::class, 'index']);
Route::post('/categories/{category}/subcategories', [SubcategoryController::class, 'store'])->middleware('auth:sanctum');
Route::put('/categories/{category}/subcategories/{subcategory}', [SubcategoryController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/categories/{category}/subcategories/{subcategory}', [SubcategoryController::class, 'destroy'])->middleware('auth:sanctum');

// Products
Route::get('/products', [ProductController::class, 'index']);
Route::post('/products', [ProductController::class, 'store'])->middleware('auth:sanctum');
Route::get('/products/{product}', [ProductController::class, 'show']);
Route::put('/products/{product}', [ProductController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/products/{product}', [ProductController::class, 'destroy'])->middleware('auth:sanctum');

// Users
Route::get('/users/{user}', [UserController::class, 'show']);

// Product Images
Route::post('/products/{product}/images', [ProductImageController::class, 'store'])->middleware('auth:sanctum');
Route::delete('/products/{product}/images/{image}', [ProductImageController::class, 'destroy'])->middleware('auth:sanctum');

// Comments
Route::get('/products/{product}/comments', [CommentController::class, 'index']);
Route::post('/products/{product}/comments', [CommentController::class, 'store'])->middleware('auth:sanctum');
Route::delete('/products/{product}/comments/{comment}', [CommentController::class, 'destroy'])->middleware('auth:sanctum');

// Attributes
Route::get('/attributes', [AttributeController::class, 'index']);
Route::post('/attributes', [AttributeController::class, 'store'])->middleware('auth:sanctum');

// Brands & Models
Route::get('/brands', [BrandController::class, 'index']);
Route::get('/brands/{brand}/models', [ModelController::class, 'index']);

// Notifications
Route::get('/notifications', [NotificationController::class, 'index'])->middleware('auth:sanctum');
Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->middleware('auth:sanctum');
Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->middleware('auth:sanctum');
Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->middleware('auth:sanctum');

// Favorites
Route::get('/favorites', [FavoriteController::class, 'index'])->middleware('auth:sanctum');
Route::post('/products/{product}/favorite', [FavoriteController::class, 'toggle'])->middleware('auth:sanctum');

// Admin routes
Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index']);

    // Admin Users
    Route::get('/users', [AdminUserController::class, 'index']);
    Route::get('/users/{user}', [AdminUserController::class, 'show']);
    Route::put('/users/{user}', [AdminUserController::class, 'update']);
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy']);
    Route::post('/users/{user}/toggle-admin', [AdminUserController::class, 'toggleAdmin']);

    // Admin Products
    Route::get('/products', [AdminProductController::class, 'index']);
    Route::get('/products/{product}', [AdminProductController::class, 'show']);
    Route::post('/products/{product}/toggle-active', [AdminProductController::class, 'toggleActive']);
    Route::post('/products/{product}/approve', [AdminProductController::class, 'approve']);
    Route::post('/products/{product}/reject', [AdminProductController::class, 'reject']);
    Route::delete('/products/{product}', [AdminProductController::class, 'destroy']);

    // Admin Categories
    Route::get('/categories', [AdminCategoryController::class, 'index']);
    Route::post('/categories', [AdminCategoryController::class, 'store']);
    Route::put('/categories/{category}', [AdminCategoryController::class, 'update']);
    Route::delete('/categories/{category}', [AdminCategoryController::class, 'destroy']);
    Route::post('/categories/{category}/toggle-active', [AdminCategoryController::class, 'toggleActive']);

    // Admin Subcategories
    Route::get('/subcategories', [AdminSubcategoryController::class, 'index']);
    Route::post('/subcategories', [AdminSubcategoryController::class, 'store']);
    Route::put('/subcategories/{subcategory}', [AdminSubcategoryController::class, 'update']);
    Route::delete('/subcategories/{subcategory}', [AdminSubcategoryController::class, 'destroy']);
    Route::post('/subcategories/{subcategory}/toggle-active', [AdminSubcategoryController::class, 'toggleActive']);

    // Admin Brands
    Route::get('/brands', [AdminBrandController::class, 'index']);
    Route::post('/brands', [AdminBrandController::class, 'store']);
    Route::put('/brands/{brand}', [AdminBrandController::class, 'update']);
    Route::delete('/brands/{brand}', [AdminBrandController::class, 'destroy']);

    // Admin Models
    Route::get('/models', [AdminModelController::class, 'index']);
    Route::post('/models', [AdminModelController::class, 'store']);
    Route::put('/models/{model}', [AdminModelController::class, 'update']);
    Route::delete('/models/{model}', [AdminModelController::class, 'destroy']);

    // Admin Attributes
    Route::get('/attributes', [AdminAttributeController::class, 'index']);
    Route::post('/attributes', [AdminAttributeController::class, 'store']);
    Route::put('/attributes/{attribute}', [AdminAttributeController::class, 'update']);
    Route::delete('/attributes/{attribute}', [AdminAttributeController::class, 'destroy']);
});
