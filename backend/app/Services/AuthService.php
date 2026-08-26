<?php

namespace App\Services;

use App\Mail\VerifyEmailCode;
use App\Models\User;
use App\Repositories\Interfaces\NotificationRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository,
        protected NotificationRepositoryInterface $notificationRepository,
    ) {}

    public function register(array $data): array
    {
        $user = $this->userRepository->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'email_verification_code' => Str::random(6),
            'email_verification_expires_at' => now()->addMinutes(30),
        ]);

        $buyerRole = \App\Models\Role::where('slug', 'buyer')->first();
        if ($buyerRole) {
            $this->userRepository->attachRole($user->id, $buyerRole->id);
        }

        Mail::to($user->email)->queue(new VerifyEmailCode($user, $user->email_verification_code));

        $token = $user->createToken('auth-token')->plainTextToken;

        $user->load('roles');

        return ['user' => $user, 'token' => $token];
    }

    public function login(string $email, string $password): array
    {
        $user = $this->userRepository->findByEmailWithRoles($email);

        if (!$user || !Hash::check($password, $user->password)) {
            throw new \Exception('Invalid credentials.');
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return ['user' => $user, 'token' => $token];
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }

    public function verifyEmail(string $email, string $code): User
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            throw new \Exception('User not found.');
        }

        if ($user->email_verified_at) {
            throw new \Exception('Email already verified.');
        }

        if ($user->email_verification_code !== $code) {
            throw new \Exception('Invalid verification code.');
        }

        if ($user->email_verification_expires_at && $user->email_verification_expires_at->isPast()) {
            throw new \Exception('Verification code has expired.');
        }

        $user->update([
            'email_verified_at' => now(),
            'email_verification_code' => null,
            'email_verification_expires_at' => null,
        ]);

        return $user;
    }

    public function resendVerification(string $email): void
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            throw new \Exception('User not found.');
        }

        if ($user->email_verified_at) {
            throw new \Exception('Email already verified.');
        }

        $code = Str::random(6);

        $user->update([
            'email_verification_code' => $code,
            'email_verification_expires_at' => now()->addMinutes(30),
        ]);

        Mail::to($user->email)->queue(new VerifyEmailCode($user, $code));
    }

    public function forgotPassword(string $email): string
    {
        $status = Password::sendResetLink(
            ['email' => $email],
            function (User $user, string $token) {
                $user->sendPasswordResetNotification($token);
            }
        );

        return $status;
    }

    public function resetPassword(array $data): string
    {
        $status = Password::reset(
            $data,
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        return $status;
    }

    public function changePassword(User $user, string $currentPassword, string $newPassword): void
    {
        if (!Hash::check($currentPassword, $user->password)) {
            throw new \Exception('Current password is incorrect.');
        }

        $user->update([
            'password' => Hash::make($newPassword),
        ]);
    }
}
