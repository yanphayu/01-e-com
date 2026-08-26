<?php

namespace App\Middlewares;

use Closure;
use Illuminate\Http\Request;

class EnsureSellerNotSuspended
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $sellerProfile = $user->sellerProfile;

        if (!$sellerProfile) {
            return response()->json(['message' => 'Seller profile not found.'], 404);
        }

        if ($sellerProfile->is_suspended) {
            return response()->json(['message' => 'Your seller account has been suspended.'], 403);
        }

        return $next($request);
    }
}
