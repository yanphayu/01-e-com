<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Notifications\ProductStatusUpdated;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::with(['user', 'subcategory.category', 'images']);

        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('category_id')) {
            $query->whereHas('subcategory', function ($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }

        $products = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    public function show(Product $product): JsonResponse
    {
        $product->load(['user.profile', 'subcategory.category', 'detail.brand', 'detail.model', 'images', 'phones', 'productAttributes.attribute', 'comments.user']);

        return response()->json([
            'success' => true,
            'data' => $product,
        ]);
    }

    public function toggleActive(Product $product): JsonResponse
    {
        $product->update(['is_active' => ! $product->is_active]);

        return response()->json([
            'success' => true,
            'message' => 'Product status updated.',
            'data' => ['is_active' => $product->is_active],
        ]);
    }

    public function approve(Product $product): JsonResponse
    {
        $product->update(['status' => 'approved', 'is_active' => true]);
        $this->notifyOwner($product);

        return response()->json([
            'success' => true,
            'message' => 'Product approved and published.',
            'data' => ['status' => $product->status],
        ]);
    }

    public function reject(Product $product): JsonResponse
    {
        $product->update(['status' => 'rejected', 'is_active' => false]);
        $this->notifyOwner($product);

        return response()->json([
            'success' => true,
            'message' => 'Product rejected.',
            'data' => ['status' => $product->status],
        ]);
    }

    private function notifyOwner(Product $product): void
    {
        try {
            $product->user->notify(new ProductStatusUpdated($product));
        } catch (\Exception $e) {
            // Broadcast may fail if Reverb is not running — notification is still saved to DB
        }
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully.',
        ]);
    }
}
