<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function __construct(
        protected readonly CategoryService $categoryService,
    ) {}

    public function index(): JsonResponse
    {
        try {
            $categories = $this->categoryService->getTree();

            return response()->json($categories, 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $category = $this->categoryService->getById($id);

            return response()->json($category, 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function store(CreateCategoryRequest $request): JsonResponse
    {
        try {
            $category = $this->categoryService->create($request->validated());

            return response()->json([
                'message' => 'Category created successfully.',
                'category' => $category,
            ], 201, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function update(UpdateCategoryRequest $request, string $id): JsonResponse
    {
        try {
            $category = $this->categoryService->update($id, $request->validated());

            return response()->json([
                'message' => 'Category updated successfully.',
                'category' => $category,
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
            $this->categoryService->delete($id);

            return response()->json([
                'message' => 'Category deleted successfully.',
            ], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }
    }
}
