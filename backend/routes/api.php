<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/', function (Request $request) {
    $messages = [
        'en' => 'API is working',
        'kh' => 'API កំពុងដំណើរការ',
    ];

    $lang = $request->query('lang');

    return response()->json([
        'message' => $lang && isset($messages[$lang])
            ? $messages[$lang]
            : $messages,
        'users' => DB::table('users')->select('id', 'name', 'email')->get(),
    ], 200, [], JSON_UNESCAPED_UNICODE);
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/verify-email', [AuthController::class, 'verifyEmail']);
Route::post('/resend-verification', [AuthController::class, 'resendVerification']);

Route::apiResource('users', UserController::class);

Route::post('/forgot-password', [PasswordResetController::class, 'forgotPassword']);
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);
