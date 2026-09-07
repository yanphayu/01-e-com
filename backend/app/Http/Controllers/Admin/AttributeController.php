<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttributeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Attribute::query();

        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $attributes = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $attributes,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:attributes,name',
        ]);

        $attribute = Attribute::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Attribute created successfully.',
            'data' => $attribute,
        ], 201);
    }

    public function update(Request $request, Attribute $attribute): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255|unique:attributes,name,'.$attribute->id,
        ]);

        $attribute->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Attribute updated successfully.',
            'data' => $attribute->fresh(),
        ]);
    }

    public function destroy(Attribute $attribute): JsonResponse
    {
        $attribute->delete();

        return response()->json([
            'success' => true,
            'message' => 'Attribute deleted successfully.',
        ]);
    }
}
