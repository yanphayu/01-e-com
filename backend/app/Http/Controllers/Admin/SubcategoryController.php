<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subcategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubcategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Subcategory::with('category');

        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $subcategories = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $subcategories,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'has_brand' => 'boolean',
            'has_model' => 'boolean',
        ]);

        $validated['slug'] = \Str::slug($validated['name']);

        $subcategory = Subcategory::create($validated);
        $subcategory->load('category');

        return response()->json([
            'success' => true,
            'message' => 'Subcategory created successfully.',
            'data' => $subcategory,
        ], 201);
    }

    public function update(Request $request, Subcategory $subcategory): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => 'sometimes|exists:categories,id',
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
            'has_brand' => 'sometimes|boolean',
            'has_model' => 'sometimes|boolean',
        ]);

        if (isset($validated['name'])) {
            $validated['slug'] = \Str::slug($validated['name']);
        }

        $subcategory->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Subcategory updated successfully.',
            'data' => $subcategory->fresh('category'),
        ]);
    }

    public function destroy(Subcategory $subcategory): JsonResponse
    {
        $subcategory->delete();

        return response()->json([
            'success' => true,
            'message' => 'Subcategory deleted successfully.',
        ]);
    }

    public function toggleActive(Subcategory $subcategory): JsonResponse
    {
        $subcategory->update(['is_active' => ! $subcategory->is_active]);

        return response()->json([
            'success' => true,
            'message' => 'Subcategory status updated.',
            'data' => ['is_active' => $subcategory->is_active],
        ]);
    }
}
