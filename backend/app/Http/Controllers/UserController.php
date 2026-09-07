<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function show(User $user): JsonResponse
    {
        $user->load(['profile.address', 'products' => fn ($q) => $q->where('is_active', true)->where('status', 'approved')->latest(), 'products.images']);

        return response()->json([
            'success' => true,
            'data' => $user,
        ]);
    }
}
