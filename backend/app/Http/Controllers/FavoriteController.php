<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $products = $request->user()
            ->favoritedProducts()
            ->with(['user.profile', 'subcategory.category', 'images', 'detail.brand', 'detail.model'])
            ->latest('favorites.created_at')
            ->paginate(12);

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    public function toggle(Request $request, Product $product): JsonResponse
    {
        $user = $request->user();

        $existing = $user->favorites()->where('product_id', $product->id)->first();

        if ($existing) {
            $existing->delete();
            $favorited = false;
        } else {
            $user->favorites()->create(['product_id' => $product->id]);
            $favorited = true;
        }

        return response()->json([
            'success' => true,
            'data' => ['favorited' => $favorited],
        ]);
    }
}
