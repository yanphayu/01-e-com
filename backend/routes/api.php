<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register',[AuthController::class,'register']);
Route::post('/verify',[AuthController::class,'verify']);
Route::post('/resend',[AuthController::class,'resend']);
Route::post('/login',[AuthController::class,'login']);
Route::post('/logout',[AuthController::class,'logout'])->middleware('auth:sanctum');
Route::get('/me',[AuthController::class,'me'])->middleware('auth:sanctum');
Route::put('/profile',[AuthController::class,'updateProfile'])->middleware('auth:sanctum');
Route::post('/avatar',[AuthController::class,'uploadAvatar'])->middleware('auth:sanctum');
