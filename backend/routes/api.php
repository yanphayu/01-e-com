<?php

use App\Http\Controllers\API\V1\AuthController;
use App\Http\Controllers\API\V1\CartController;
use App\Http\Controllers\API\V1\CategoryController;
use App\Http\Controllers\API\V1\CouponController;
use App\Http\Controllers\API\V1\DeliveryController;
use App\Http\Controllers\API\V1\NotificationController;
use App\Http\Controllers\API\V1\OrderController;
use App\Http\Controllers\API\V1\PaymentController;
use App\Http\Controllers\API\V1\ProductController;
use App\Http\Controllers\API\V1\ReportController;
use App\Http\Controllers\API\V1\ReviewController;
use App\Http\Controllers\API\V1\SellerController;
use App\Http\Controllers\API\V1\UserController;
use App\Http\Controllers\API\V1\WishlistController;
use App\Http\Controllers\API\V1\Admin\DashboardController;
use Illuminate\Support\Facades\Route;

// Health check
Route::get('/', function () {
    return response()->json([
        'message' => 'E-Commerce Marketplace API',
        'version' => 'v1',
        'status' => 'running',
    ]);
});

// Public routes
Route::prefix('v1')->group(function () {
    // Auth
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/verify-email', [AuthController::class, 'verifyEmail']);
    Route::post('/auth/resend-verification', [AuthController::class, 'resendVerification']);
    Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);

    // Products (public)
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/featured', [ProductController::class, 'featured']);
    Route::get('/products/{slug}', [ProductController::class, 'showBySlug']);
    Route::get('/products/{id}/related', [ProductController::class, 'related']);

    // Categories (public)
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{id}', [CategoryController::class, 'show']);

    // Reviews (public)
    Route::get('/products/{productId}/reviews', [ReviewController::class, 'byProduct']);

    // Seller profiles (public)
    Route::get('/sellers', [SellerController::class, 'index']);
    Route::get('/sellers/{slug}', [SellerController::class, 'showBySlug']);
    Route::get('/sellers/{id}/products', [ProductController::class, 'bySeller']);

    // Coupon validation
    Route::post('/coupons/validate', [CouponController::class, 'validate']);

    // Authenticated routes
    Route::middleware('auth:sanctum')->group(function () {
        // Auth
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::post('/auth/change-password', [AuthController::class, 'changePassword']);
        Route::get('/auth/me', function (\Illuminate\Http\Request $request) {
            return new \App\Http\Resources\UserResource($request->user()->load('roles'));
        });

        // User Profile
        Route::put('/profile', [UserController::class, 'update']);

        // Wishlist
        Route::get('/wishlist', [WishlistController::class, 'index']);
        Route::post('/wishlist', [WishlistController::class, 'store']);
        Route::delete('/wishlist/{productId}', [WishlistController::class, 'destroy']);

        // Cart
        Route::get('/cart', [CartController::class, 'index']);
        Route::post('/cart', [CartController::class, 'addItem']);
        Route::put('/cart/{cartItemId}', [CartController::class, 'updateItem']);
        Route::delete('/cart/{cartItemId}', [CartController::class, 'removeItem']);
        Route::delete('/cart', [CartController::class, 'clear']);

        // Orders (buyer)
        Route::get('/orders', [OrderController::class, 'index']);
        Route::post('/orders', [OrderController::class, 'store']);
        Route::get('/orders/{id}', [OrderController::class, 'show']);
        Route::post('/orders/{id}/cancel', [OrderController::class, 'cancel']);

        // Payments
        Route::post('/payments', [PaymentController::class, 'store']);
        Route::get('/payments/history', [PaymentController::class, 'history']);
        Route::get('/orders/{orderId}/payment', [PaymentController::class, 'show']);

        // Reviews
        Route::post('/reviews', [ReviewController::class, 'store']);

        // Notifications
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
        Route::put('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::put('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);

        // Reports
        Route::post('/reports', [ReportController::class, 'store']);

        // Seller routes
        Route::post('/seller/profile', [SellerController::class, 'store']);
        Route::put('/seller/profile/{id}', [SellerController::class, 'update']);
        Route::get('/seller/dashboard', [OrderController::class, 'dashboard']);
        Route::get('/seller/revenue', [OrderController::class, 'revenueReport']);

        // Seller product management
        Route::post('/seller/products', [ProductController::class, 'store']);
        Route::put('/seller/products/{id}', [ProductController::class, 'update']);
        Route::delete('/seller/products/{id}', [ProductController::class, 'destroy']);

        // Seller order management
        Route::post('/seller/orders/{id}/accept', [OrderController::class, 'accept']);
        Route::post('/seller/orders/{id}/reject', [OrderController::class, 'reject']);
        Route::post('/seller/orders/{id}/pack', [OrderController::class, 'pack']);
        Route::post('/seller/orders/{id}/ship', [OrderController::class, 'ship']);

        // Seller coupons
        Route::get('/seller/coupons', [CouponController::class, 'index']);
        Route::post('/seller/coupons', [CouponController::class, 'store']);
        Route::put('/seller/coupons/{id}', [CouponController::class, 'update']);
        Route::delete('/seller/coupons/{id}', [CouponController::class, 'destroy']);

        // Courier routes
        Route::post('/courier/profile', [\App\Http\Controllers\API\V1\DeliveryController::class, 'store']);
        Route::get('/courier/deliveries', [DeliveryController::class, 'index']);
        Route::post('/courier/deliveries/{id}/accept', [DeliveryController::class, 'accept']);
        Route::post('/courier/deliveries/{id}/pickup', [DeliveryController::class, 'pickup']);
        Route::post('/courier/deliveries/{id}/transit', [DeliveryController::class, 'inTransit']);
        Route::post('/courier/deliveries/{id}/complete', [DeliveryController::class, 'complete']);
        Route::post('/courier/deliveries/{id}/fail', [DeliveryController::class, 'fail']);
        Route::get('/courier/earnings', [DeliveryController::class, 'earnings']);
    });

    // Admin routes
    Route::middleware(['auth:sanctum'])->prefix('admin')->group(function () {
        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'stats']);
        Route::get('/dashboard/recent-orders', [DashboardController::class, 'recentOrders']);
        Route::get('/dashboard/top-sellers', [DashboardController::class, 'topSellers']);
        Route::get('/dashboard/top-products', [DashboardController::class, 'topProducts']);

        // User management
        Route::get('/users', [UserController::class, 'index']);
        Route::get('/users/{id}', [UserController::class, 'show']);
        Route::post('/users/{id}/suspend', [UserController::class, 'suspend']);
        Route::post('/users/{id}/unsuspend', [UserController::class, 'unsuspend']);

        // Seller management
        Route::get('/sellers', [SellerController::class, 'index']);
        Route::get('/sellers/{id}', [SellerController::class, 'show']);
        Route::post('/sellers/{id}/suspend', [SellerController::class, 'suspend']);
        Route::post('/sellers/{id}/unsuspend', [SellerController::class, 'unsuspend']);

        // Category management
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::put('/categories/{id}', [CategoryController::class, 'update']);
        Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

        // Product management
        Route::post('/products/{id}/suspend', [ProductController::class, 'suspend']);
        Route::post('/products/{id}/activate', [ProductController::class, 'activate']);

        // Review management
        Route::put('/reviews/{id}/approve', [ReviewController::class, 'approve']);
        Route::put('/reviews/{id}/disapprove', [ReviewController::class, 'disapprove']);
        Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);

        // Payment management
        Route::post('/payments/{paymentId}/refund', [PaymentController::class, 'refund']);

        // Report management
        Route::get('/reports', [ReportController::class, 'index']);
        Route::put('/reports/{id}', [ReportController::class, 'update']);
        Route::delete('/reports/{id}', [ReportController::class, 'destroy']);
    });
});
