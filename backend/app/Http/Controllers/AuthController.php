<?php

namespace App\Http\Controllers;

use App\Mail\SendEmailVerify;
use App\Models\Otp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    // register
    public function register(Request $request)
    {
        $validate = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed'
        ]);

        $user = User::create([
            'name' => $validate['name'],
            'email' => $validate['email'],
            'password' => Hash::make($validate['password'])
        ]);

        $otp = random_int(000000, 999999);

        Otp::create([
            'user_id' => $user['id'],
            'otp' => $otp,
            'expires_at' => now()->addMinute(1)
        ]);

        Mail::to($user['email'])->send(new SendEmailVerify($otp));

        return response()->json([
            'success' => true,
            'message' => 'Please verify your email.',
            'data' => $user
        ]);
    }

    // verify
    public function verify(Request $request)
    {
        $validate = $request->validate([
            'user_id' => 'required',
            'email' => 'required|email',
            'otp' => 'required|string|digits:6'
        ]);

        $user = User::where('id', $validate['user_id'])
            ->where('email', $validate['email'])
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.'
            ]);
        }

        $otp = Otp::where('user_id', $user['id'])
            ->where('otp', $validate['otp'])
            ->first();

        if (!$otp) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP.'
            ]);
        }

        if (now()->greaterThan($otp['expires_at'])) {
            return response()->json([
                'success' => false,
                'message' => 'OTP has expired.'
            ]);
        }

        $token = $user->createToken('verify_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Verify successfully.',
            'data' => $user,
            'token' => $token
        ]);
    }

    //resend
    public function resend(Request $request)
    {
        $validate = $request->validate([
            'user_id' => 'required',
            'email' => 'required|email',
        ]);

        $user = User::where('id', $validate['user_id'])
            ->where('email', $validate['email'])
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.'
            ]);
        }

        $otp = random_int(000000, 999999);

        Otp::create([
            'user_id' => $user['id'],
            'otp' => $otp,
            'expires_at' => now()->addMinute(1)
        ]);

        Mail::to($user['email'])->send(new SendEmailVerify($otp));

        return response()->json([
            'success' => true,
            'message' => 'Please verify your email.',
            'data' => $user
        ]);
    }

    // login
    public function login(Request $request)
    {
        $validate = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        if (
            !Auth::attempt([
                'email' => $validate['email'],
                'password' => $validate['password']
            ])
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials.'
            ]);
        }

        $user = Auth::user();

        $token = $user->createToken('login_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successfully.',
            'data' => $user,
            'token'=> $token
        ]);
    }

    // current user
    public function me(Request $request){
        return response()->json([
            'success' => true,
            'data' => $request->user()
        ]);
    }

    // logout
    public function logout(Request $request){
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout successfully.'
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
            'twitter' => 'nullable|url'
        ]);

        $user = $request->user();

        $name = $user->name;
        if (isset($validate['first_name']) && isset($validate['last_name'])) {
            $name = trim($validate['first_name'] . ' ' . $validate['last_name']);
        } elseif (isset($validate['first_name'])) {
            $name = trim($validate['first_name'] . ' ' . explode(' ', $user->name)[1] ?? $user->name);
        } elseif (isset($validate['last_name'])) {
            $name = trim((explode(' ', $user->name)[0] ?? '') . ' ' . $validate['last_name']);
        }

        $user->name = $name;

        foreach (['phone', 'birth_date', 'address', 'latitude', 'longitude', 'avatar', 'facebook', 'instagram', 'twitter'] as $field) {
            if (array_key_exists($field, $validate)) {
                $user->{$field} = $validate[$field];
            }
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'data' => $user
        ]);
    }

    // upload avatar
    public function uploadAvatar(Request $request)
    {
        $validate = $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048'
        ]);

        $path = $request->file('avatar')->store('avatars', 'public');

        $user = $request->user();
        $user->avatar = asset('storage/' . $path);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Avatar uploaded successfully.',
            'data' => $user
        ]);
    }
}
