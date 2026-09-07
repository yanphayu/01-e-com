<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ModelController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ProductModel::with('brand');

        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        if ($request->has('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        $models = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $models,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'brand_id' => 'required|exists:brands,id',
            'name' => 'required|string|max:255',
            'attribute_ids' => 'nullable|array',
            'attribute_ids.*' => 'exists:attributes,id',
        ]);

        $attributeIds = $validated['attribute_ids'] ?? [];
        unset($validated['attribute_ids']);

        $model = ProductModel::create($validated);
        $model->attributes()->sync($attributeIds);
        $model->load('brand', 'attributes');

        return response()->json([
            'success' => true,
            'message' => 'Model created successfully.',
            'data' => $model,
        ], 201);
    }

    public function update(Request $request, ProductModel $model): JsonResponse
    {
        $validated = $request->validate([
            'brand_id' => 'sometimes|exists:brands,id',
            'name' => 'sometimes|required|string|max:255',
            'attribute_ids' => 'nullable|array',
            'attribute_ids.*' => 'exists:attributes,id',
        ]);

        $attributeIds = $validated['attribute_ids'] ?? null;
        unset($validated['attribute_ids']);

        $model->update($validated);

        if ($attributeIds !== null) {
            $model->attributes()->sync($attributeIds);
        }

        return response()->json([
            'success' => true,
            'message' => 'Model updated successfully.',
            'data' => $model->fresh('brand', 'attributes'),
        ]);
    }

    public function destroy(ProductModel $model): JsonResponse
    {
        $model->delete();

        return response()->json([
            'success' => true,
            'message' => 'Model deleted successfully.',
        ]);
    }
}
