<?php

namespace App\Http\Controllers;

use App\Mail\SendEmailVerify;
use App\Mail\SendPasswordReset;
use App\Models\Address;
use App\Models\Otp;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    // register
    public function register(Request $request)
    {
        $validate = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $validate['name'],
            'email' => $validate['email'],
            'password' => $validate['password'],
        ]);

        $otp = random_int(000000, 999999);

        Otp::create([
            'user_id' => $user['id'],
            'type' => 'email_verify',
            'otp' => $otp,
            'expires_at' => now()->addMinute(1),
        ]);

        Mail::to($user['email'])->send(new SendEmailVerify($otp));

        return response()->json([
            'success' => true,
            'message' => 'Please verify your email.',
            'data' => $user,
        ]);
    }

    // verify
    public function verify(Request $request)
    {
        $validate = $request->validate([
            'user_id' => 'required',
            'email' => 'required|email',
            'otp' => 'required|string|digits:6',
        ]);

        $user = User::where('id', $validate['user_id'])
            ->where('email', $validate['email'])
            ->first();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ]);
        }

        $otp = Otp::where('user_id', $user['id'])
            ->where('otp', $validate['otp'])
            ->first();

        if (! $otp) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP.',
            ]);
        }

        if (now()->greaterThan($otp['expires_at'])) {
            return response()->json([
                'success' => false,
                'message' => 'OTP has expired.',
            ]);
        }

        $token = $user->createToken('verify_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Verify successfully.',
            'data' => $user,
            'token' => $token,
        ]);
    }

    // resend
    public function resend(Request $request)
    {
        $validate = $request->validate([
            'user_id' => 'required',
            'email' => 'required|email',
        ]);

        $user = User::where('id', $validate['user_id'])
            ->where('email', $validate['email'])
            ->first();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ]);
        }

        $otp = random_int(000000, 999999);

        Otp::create([
            'user_id' => $user['id'],
            'type' => 'email_verify',
            'otp' => $otp,
            'expires_at' => now()->addMinute(1),
        ]);

        Mail::to($user['email'])->send(new SendEmailVerify($otp));

        return response()->json([
            'success' => true,
            'message' => 'Please verify your email.',
            'data' => $user,
        ]);
    }

    // login
    public function login(Request $request)
    {
        $validate = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (
            ! Auth::attempt([
                'email' => $validate['email'],
                'password' => $validate['password'],
            ])
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials.',
            ]);
        }

        $user = Auth::user();

        $token = $user->createToken('login_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successfully.',
            'data' => $user,
            'token' => $token,
        ]);
    }

    // current user
    public function me(Request $request)
    {
        $user = $request->user()->load(['profile.address']);

        return response()->json([
            'success' => true,
            'data' => $user,
        ]);
    }

    // logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout successfully.',
        ]);
    }

    // update profile
    public function updateProfile(Request $request)
    {
        $validate = $request->validate([
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date',
            'address' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'avatar' => 'nullable|url',
            'facebook' => 'nullable|url',
            'instagram' => 'nullable|url',
            'twitter' => 'nullable|url',
        ]);

        $user = $request->user();

        // Update user name
        $name = $user->name;
        if (isset($validate['first_name']) && isset($validate['last_name'])) {
            $name = trim($validate['first_name'].' '.$validate['last_name']);
        } elseif (isset($validate['first_name'])) {
            $name = trim($validate['first_name'].' '.explode(' ', $user->name)[1] ?? $user->name);
        } elseif (isset($validate['last_name'])) {
            $name = trim((explode(' ', $user->name)[0] ?? '').' '.$validate['last_name']);
        }

        $user->name = $name;
        $user->save();

        // Update or create profile
        $profile = $user->profile()->firstOrCreate([]);

        $profileFields = ['phone', 'birth_date', 'avatar', 'facebook', 'instagram', 'twitter'];
        foreach ($profileFields as $field) {
            if (array_key_exists($field, $validate)) {
                $profile->{$field} = $validate[$field];
            }
        }
        $profile->save();

        // Update or create address
        $addressFields = ['address', 'latitude', 'longitude'];
        $hasAddressData = array_reduce($addressFields, fn ($carry, $field) => $carry || array_key_exists($field, $validate), false);

        if ($hasAddressData) {
            $address = $profile->address()->firstOrCreate([]);

            foreach ($addressFields as $field) {
                if (array_key_exists($field, $validate)) {
                    $address->{$field} = $validate[$field];
                }
            }
            $address->save();
        }

        $user->load(['profile.address']);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'data' => $user,
        ]);
    }

    // upload avatar
    public function uploadAvatar(Request $request)
    {
        $validate = $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $path = $request->file('avatar')->store('avatars', 'public');

        $user = $request->user();
        $profile = $user->profile()->firstOrCreate([]);
        $profile->avatar = asset('storage/'.$path);
        $profile->save();

        $user->load(['profile.address']);

        return response()->json([
            'success' => true,
            'message' => 'Avatar uploaded successfully.',
            'data' => $user,
        ]);
    }

    // upload cover image
    public function uploadCoverImage(Request $request)
    {
        $validate = $request->validate([
            'cover_image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $path = $request->file('cover_image')->store('covers', 'public');

        $user = $request->user();
        $profile = $user->profile()->firstOrCreate([]);
        $profile->cover_image = asset('storage/'.$path);
        $profile->save();

        $user->load(['profile.address']);

        return response()->json([
            'success' => true,
            'message' => 'Cover image uploaded successfully.',
            'data' => $user,
        ]);
    }

    // delete account
    public function deleteAccount(Request $request)
    {
        $user = $request->user();

        $user->tokens()->delete();
        $user->otps()->delete();

        // Delete profile and address
        if ($user->profile) {
            if ($user->profile->address) {
                $user->profile->address()->delete();
            }
            $user->profile()->delete();
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Account deleted successfully.',
        ]);
    }

    // forgot password
    public function forgotPassword(Request $request)
    {
        $validate = $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $validate['email'])->first();

        if (! $user) {
            return response()->json([
                'success' => true,
                'message' => 'If the email exists, a verification code has been sent.',
            ]);
        }

        Otp::where('user_id', $user->id)
            ->where('type', 'password_reset')
            ->delete();

        $otp = random_int(000000, 999999);

        Otp::create([
            'user_id' => $user->id,
            'type' => 'password_reset',
            'otp' => $otp,
            'expires_at' => now()->addMinutes(15),
        ]);

        Mail::to($user->email)->send(new SendPasswordReset($otp));

        return response()->json([
            'success' => true,
            'message' => 'If the email exists, a verification code has been sent.',
        ]);
    }

    // reset password
    public function resetPassword(Request $request)
    {
        $validate = $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|digits:6',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::where('email', $validate['email'])->first();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials.',
            ]);
        }

        $otp = Otp::where('user_id', $user->id)
            ->where('type', 'password_reset')
            ->where('otp', $validate['otp'])
            ->first();

        if (! $otp) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP.',
            ]);
        }

        if (now()->greaterThan($otp->expires_at)) {
            return response()->json([
                'success' => false,
                'message' => 'OTP has expired.',
            ]);
        }

        $user->password = $validate['password'];
        $user->save();

        Otp::where('user_id', $user->id)
            ->where('type', 'password_reset')
            ->delete();

        $user->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Password has been reset successfully.',
        ]);
    }
}
