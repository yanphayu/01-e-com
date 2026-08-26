<?php

namespace App\Http\Controllers;

use App\Mail\VerifyEmailCode;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    // POST /api/login
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials.',
            ], 401, [], JSON_UNESCAPED_UNICODE);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful.',
            'token' => $token,
            'user' => $user->only(['id', 'name', 'email']),
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    // POST /api/register
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $verificationCode = (string) random_int(100000, 999999);

        // Remove any previous unverified registration with this email so it
        // doesn't block re-registration via the unique email constraint.
        User::where('email', $validated['email'])
            ->whereNull('email_verified_at')
            ->delete();

        $user = User::create([
            'email' => $validated['email'],
            'password' => $validated['password'],
            'email_verification_code' => $verificationCode,
            'email_verification_expires_at' => now()->addMinute(),
        ]);

        Mail::to($user->email)->send(new VerifyEmailCode($user, $verificationCode));

        return response()->json([
            'message' => 'Account created successfully. Please verify your email.',
            'data' => $user->only(['id', 'name', 'email', 'created_at']),
        ], 201, [], JSON_UNESCAPED_UNICODE);
    }

    // POST /api/verify-email
    public function verifyEmail(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'code' => ['required', 'string'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user) {
            return response()->json([
                'message' => 'User not found.',
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }

        if ($user->email_verified_at) {
            return response()->json([
                'message' => 'Email already verified.',
            ], 200, [], JSON_UNESCAPED_UNICODE);
        }

        if ($user->email_verification_expires_at && $user->email_verification_expires_at->isPast()) {
            return response()->json([
                'message' => 'This code has expired. Please request a new one.',
            ], 422, [], JSON_UNESCAPED_UNICODE);
        }

        if ($user->email_verification_code !== $validated['code']) {
            return response()->json([
                'message' => 'Invalid verification code.',
            ], 422, [], JSON_UNESCAPED_UNICODE);
        }

        $user->update([
            'email_verified_at' => now(),
            'email_verification_code' => null,
        ]);

        return response()->json([
            'message' => 'Email verified successfully.',
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    // POST /api/resend-verification
    public function resendVerification(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user) {
            return response()->json([
                'message' => 'User not found.',
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }

        if ($user->email_verified_at) {
            return response()->json([
                'message' => 'Email already verified.',
            ], 200, [], JSON_UNESCAPED_UNICODE);
        }

        $verificationCode = (string) random_int(100000, 999999);

        $user->update([
            'email_verification_code' => $verificationCode,
            'email_verification_expires_at' => now()->addMinute(),
        ]);

        Mail::to($user->email)->send(new VerifyEmailCode($user, $verificationCode));

        return response()->json([
            'message' => 'A new verification code has been sent.',
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }
}
