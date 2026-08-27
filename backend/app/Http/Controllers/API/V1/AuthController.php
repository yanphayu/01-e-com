<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\VerifyEmailRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        protected readonly AuthService $authService,
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->register($request->validated());

            return response()->json([
                'message' => 'Account created successfully.',
                'user' => $result['user'],
                'token' => $result['token'],
            ], 201, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->login(
                $request->input('email'),
                $request->input('password'),
            );

            return response()->json([
                'message' => 'Login successful.',
                'token' => $result['token'],
                'user' => $result['user'],
            ], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 401, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        try {
            $this->authService->logout($request->user());

            return response()->json([
                'message' => 'Logged out successfully.',
            ], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function verifyEmail(VerifyEmailRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->verifyEmail(
                $request->input('email'),
                $request->input('code'),
            );

            return response()->json([
                'message' => 'Email verified successfully.',
                'token' => $result['token'],
                'user' => $result['user'],
            ], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function resendVerification(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        try {
            $this->authService->resendVerification($validated['email']);

            return response()->json([
                'message' => 'A new verification code has been sent.',
            ], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        try {
            $this->authService->forgotPassword($request->input('email'));

            return response()->json([
                'message' => 'Password reset link sent.',
            ], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        try {
            $this->authService->resetPassword($request->validated());

            return response()->json([
                'message' => 'Password has been reset successfully.',
            ], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function changePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        try {
            $this->authService->changePassword(
                $request->user(),
                $validated['current_password'],
                $validated['new_password'],
            );

            return response()->json([
                'message' => 'Password changed successfully.',
            ], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422, [], JSON_UNESCAPED_UNICODE);
        }
    }
}
