<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Brand::withCount('models')->orderBy('name');

        if ($request->has('subcategory_id')) {
            $query->where('subcategory_id', $request->subcategory_id);
        }

        $brands = $query->get();

        return response()->json([
            'success' => true,
            'message' => 'Brands retrieved successfully',
            'data' => $brands,
        ]);
    }
}
