<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| PasswordResetController
|--------------------------------------------------------------------------
| Handles "forgot password" flow for users in 2 steps:
|
|   1) POST /api/forgot-password  {email}
|      -> Sends a reset link to the user's email.
|      -> The link contains a signed token stored in password_reset_tokens table.
|      -> With MAIL_MAILER=log, the email content goes to storage/logs/laravel.log
|
|   2) POST /api/reset-password   {token, email, password, password_confirmation}
|      -> Verifies the token is valid & not expired (default: 60 minutes).
|      -> Updates the user's password in the database.
*/

class PasswordResetController extends Controller
{
    // POST /api/forgot-password
    // Step 1: User enters their email, we send them a reset link.
    public function forgotPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        // Creates a token row in "password_reset_tokens" table and sends the email.
        $status = Password::sendResetLink($validated);

        // Always return the same generic message so attackers cannot
        // discover which emails are registered in the database.
        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => 'Reset link sent to your email.',
            ], 200, [], JSON_UNESCAPED_UNICODE);
        }

        return response()->json([
            'message' => __($status),
        ], 400, [], JSON_UNESCAPED_UNICODE);
    }

    // POST /api/reset-password
    // Step 2: User submits token + new password from the email link.
    public function resetPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // Password::reset checks that token+email match a valid row in
        // password_reset_tokens. If yes, it calls our closure to update the DB.
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                // The model's "hashed" cast encrypts the password automatically.
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();

                // Fires event so other parts of the app can react (e.g. invalidate old sessions).
                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'message' => 'Password has been reset successfully.',
            ], 200, [], JSON_UNESCAPED_UNICODE);
        }

        // Invalid or expired token -> 400 Bad Request with reason (e.g. "This password reset token is invalid.")
        return response()->json([
            'message' => __($status),
        ], 400, [], JSON_UNESCAPED_UNICODE);
    }
}
