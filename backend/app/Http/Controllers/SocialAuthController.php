<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    // redirect to Google
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    // handle Google callback
    public function handleGoogleCallback()
    {
        $this->configureCurlCaCert();

        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
        } catch (Exception $e) {
            Log::error('Google OAuth callback failed', ['error' => $e->getMessage()]);

            return redirect()->to($this->frontendUrl('/login?error='.rawurlencode($e->getMessage())));
        }

        $user = User::where('provider', 'google')->where('provider_id', $googleUser->getId())->first();

        if (! $user && $googleUser->getEmail() !== null) {
            $user = User::where('email', $googleUser->getEmail())->first();
        }

        $isNewUser = false;

        if (! $user) {
            $isNewUser = true;

            $user = User::create([
                'name' => $googleUser->getName() ?: $googleUser->getEmail(),
                'email' => $googleUser->getEmail(),
                'password' => null,
                'provider' => 'google',
                'provider_id' => $googleUser->getId(),
                'email_verified_at' => now(),
            ]);
        } else {
            $user->update([
                'provider' => 'google',
                'provider_id' => $googleUser->getId(),
                'email_verified_at' => $user->email_verified_at ?? now(),
            ]);
        }

        $token = $user->createToken('google_login_token')->plainTextToken;

        return redirect()->to($this->frontendUrl("/auth/callback?token={$token}&new=".($isNewUser ? '1' : '0')));
    }

    private function frontendUrl(string $path): string
    {
        return rtrim(config('app.frontend_url', 'http://localhost:5173'), '/').$path;
    }

    private function configureCurlCaCert(): void
    {
        if (ini_get('curl.cainfo') !== '') {
            return;
        }

        $cacert = config('services.google.cacert');

        if (is_string($cacert) && is_file($cacert)) {
            ini_set('curl.cainfo', $cacert);
        }
    }
}
