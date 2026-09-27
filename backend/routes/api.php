<?php

use App\Http\Controllers\AdController;
use App\Http\Controllers\AttributeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuthPanelController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\ModelController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductImageController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SocialAuthController;
use App\Http\Controllers\SubcategoryController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Search
Route::get('/search', SearchController::class);

// Auth
Route::get('/auth-panel', [AuthPanelController::class, 'show']);
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:otp-send');
Route::post('/verify', [AuthController::class, 'verify'])->middleware('throttle:otp-verify');
Route::post('/resend', [AuthController::class, 'resend'])->middleware('throttle:otp-send');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:otp-send');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:otp-verify');

// Social Auth
Route::get('/auth/google/redirect', [SocialAuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [SocialAuthController::class, 'handleGoogleCallback']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/me', [AuthController::class, 'me'])->middleware('auth:sanctum');
Route::put('/profile', [AuthController::class, 'updateProfile'])->middleware('auth:sanctum');
Route::delete('/profile', [AuthController::class, 'deleteAccount'])->middleware('auth:sanctum');
Route::post('/profile/delete-otp', [AuthController::class, 'sendDeleteOtp'])->middleware(['auth:sanctum', 'throttle:otp-send']);
Route::post('/avatar', [AuthController::class, 'uploadAvatar'])->middleware('auth:sanctum');
Route::post('/cover-image', [AuthController::class, 'uploadCoverImage'])->middleware('auth:sanctum');

// Ads
Route::get('/ads', [AdController::class, 'index']);

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
Route::put('/products/{product}/images/{image}', [ProductImageController::class, 'update'])->middleware('auth:sanctum');
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
Route::delete('/notifications/read', [NotificationController::class, 'clearRead'])->middleware('auth:sanctum');
Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->middleware('auth:sanctum');
Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->middleware('auth:sanctum');

// Favorites
Route::get('/favorites', [FavoriteController::class, 'index'])->middleware('auth:sanctum');
Route::post('/products/{product}/favorite', [FavoriteController::class, 'toggle'])->middleware('auth:sanctum');

// Reports
Route::post('/products/{product}/reports', [ReportController::class, 'store'])->middleware('auth:sanctum');

// Chat
Route::get('/conversations', [ChatController::class, 'index'])->middleware('auth:sanctum');
Route::post('/conversations', [ChatController::class, 'store'])->middleware('auth:sanctum');
Route::patch('/conversations/{conversation}', [ChatController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/conversations/{conversation}', [ChatController::class, 'destroy'])->middleware('auth:sanctum');
Route::post('/conversations/{conversation}/unread', [ChatController::class, 'markUnread'])->middleware('auth:sanctum');
Route::post('/conversations/{conversation}/block', [ChatController::class, 'block'])->middleware('auth:sanctum');
Route::get('/conversations/{conversation}/messages', [ChatController::class, 'messages'])->middleware('auth:sanctum');
Route::post('/conversations/{conversation}/messages', [ChatController::class, 'send'])->middleware('auth:sanctum');
Route::get('/chat/search-users', [ChatController::class, 'searchUsers'])->middleware('auth:sanctum');
Route::get('/chat/blocked-users', [ChatController::class, 'blockedUsers'])->middleware('auth:sanctum');
Route::delete('/chat/blocked-users/{user}', [ChatController::class, 'unblockUser'])->middleware('auth:sanctum');

// Messages
Route::patch('/messages/{message}', [ChatController::class, 'updateMessage'])->middleware('auth:sanctum');
Route::delete('/messages/{message}', [ChatController::class, 'destroyMessage'])->middleware('auth:sanctum');
Route::post('/messages/{message}/pin', [ChatController::class, 'pinMessage'])->middleware('auth:sanctum');
Route::post('/messages/{message}/react', [ChatController::class, 'reactToMessage'])->middleware('auth:sanctum');
