<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubcategoryController extends Controller
{
    public function index(Category $category): JsonResponse
    {
        $subcategories = $category->subcategories()
            ->where('is_active', true)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Subcategories retrieved successfully',
            'data' => $subcategories,
        ]);
    }

    public function store(Request $request, Category $category): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:subcategories,slug',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $subcategory = $category->subcategories()->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Subcategory created successfully',
            'data' => $subcategory,
        ], 201);
    }

    public function update(Request $request, Category $category, Subcategory $subcategory): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|max:255|unique:subcategories,slug,'.$subcategory->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $subcategory->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Subcategory updated successfully',
            'data' => $subcategory,
        ]);
    }

    public function destroy(Category $category, Subcategory $subcategory): JsonResponse
    {
        $subcategory->delete();

        return response()->json([
            'success' => true,
            'message' => 'Subcategory deleted successfully',
        ]);
    }
}
