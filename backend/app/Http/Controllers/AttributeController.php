<?php

namespace App\Http\Controllers;

use App\Models\Attribute;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttributeController extends Controller
{
    public function index(): JsonResponse
    {
        $attributes = Attribute::all();

        return response()->json([
            'success' => true,
            'message' => 'Attributes retrieved successfully',
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
            'message' => 'Attribute created successfully',
            'data' => $attribute,
        ], 201);
    }
}
