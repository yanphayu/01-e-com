<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Seller\CreateProductRequest;
use App\Http\Requests\Seller\UpdateProductRequest;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(
        protected readonly ProductService $productService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'category_id', 'seller_id', 'min_price', 'max_price',
                'search', 'status', 'sort_by', 'sort_order',
            ]);
            $perPage = $request->input('per_page', 15);

            $products = $this->productService->search($filters, $perPage);

            return response()->json($products, 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $product = $this->productService->getById($id);

            return response()->json($product, 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function showBySlug(string $slug): JsonResponse
    {
        try {
            $product = $this->productService->getBySlug($slug);

            return response()->json($product, 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function store(CreateProductRequest $request): JsonResponse
    {
        try {
            $product = $this->productService->create($request->validated());

            return response()->json([
                'message' => 'Product created successfully.',
                'product' => $product,
            ], 201, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function update(UpdateProductRequest $request, string $id): JsonResponse
    {
        try {
            $product = $this->productService->update($id, $request->validated());

            return response()->json([
                'message' => 'Product updated successfully.',
                'product' => $product,
            ], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $this->productService->delete($id);

            return response()->json([
                'message' => 'Product deleted successfully.',
            ], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function featured(Request $request): JsonResponse
    {
        try {
            $limit = $request->input('limit', 10);

            $products = $this->productService->getFeatured($limit);

            return response()->json($products, 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function related(string $id): JsonResponse
    {
        try {
            $products = $this->productService->getRelated($id);

            return response()->json($products, 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function bySeller(string $sellerId, Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 15);

            $products = $this->productService->getBySeller($sellerId, $perPage);

            return response()->json($products, 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function suspend(string $id): JsonResponse
    {
        try {
            $product = $this->productService->suspend($id);

            return response()->json([
                'message' => 'Product suspended successfully.',
                'product' => $product,
            ], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function activate(string $id): JsonResponse
    {
        try {
            $product = $this->productService->activate($id);

            return response()->json([
                'message' => 'Product activated successfully.',
                'product' => $product,
            ], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }
    }
}
