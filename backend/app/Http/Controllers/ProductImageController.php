<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductImageController extends Controller
{
    public function store(Request $request, Product $product): JsonResponse
    {
        if ($request->user()->id !== $product->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $validated = $request->validate([
            'image' => 'required|image|max:5120',
            'is_primary' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $image = $product->images()->create([
            'image' => $request->file('image')->store('products/'.$product->id, 'public'),
            'is_primary' => $validated['is_primary'] ?? false,
            'sort_order' => $validated['sort_order'] ?? $product->images()->count(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Image uploaded successfully',
            'data' => $image,
        ], 201);
    }

    public function update(Request $request, Product $product, ProductImage $image): JsonResponse
    {
        if ($request->user()->id !== $product->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $validated = $request->validate([
            'is_primary' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        if (isset($validated['is_primary']) && $validated['is_primary']) {
            $product->images()->where('id', '!=', $image->id)->update(['is_primary' => false]);
        }

        $image->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Image updated successfully',
            'data' => $image,
        ]);
    }

    public function destroy(Request $request, Product $product, ProductImage $image): JsonResponse
    {
        if ($request->user()->id !== $product->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $path = $image->image;
        $image->delete();

        $disk = Storage::disk('public');
        if ($disk->exists($path)) {
            $disk->delete($path);
        }

        return response()->json([
            'success' => true,
            'message' => 'Image deleted successfully',
        ]);
    }
}
