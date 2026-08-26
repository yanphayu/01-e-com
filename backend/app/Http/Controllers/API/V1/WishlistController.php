<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\WishlistResource;
use App\Models\Wishlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $wishlists = Wishlist::where('user_id', $request->user()->id)
                ->with('product.images', 'product.seller')
                ->paginate($request->query('per_page', 15));
            return response()->json(WishlistResource::collection($wishlists));
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'product_id' => 'required|exists:products,id',
            ]);

            $existing = Wishlist::where('user_id', $request->user()->id)
                ->where('product_id', $request->input('product_id'))
                ->first();

            if ($existing) {
                return response()->json(['message' => 'Product already in wishlist.'], 409);
            }

            $wishlist = Wishlist::create([
                'user_id' => $request->user()->id,
                'product_id' => $request->input('product_id'),
            ]);

            return response()->json(
                new WishlistResource($wishlist->load('product.images', 'product.seller')),
                201
            );
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function destroy(string $productId): JsonResponse
    {
        try {
            $deleted = Wishlist::where('user_id', request()->user()->id)
                ->where('product_id', $productId)
                ->delete();
            if (!$deleted) {
                return response()->json(['message' => 'Product not found in wishlist'], 404);
            }
            return response()->json(['message' => 'Product removed from wishlist']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
