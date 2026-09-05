<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\JsonResponse;

class ModelController extends Controller
{
    public function index(Brand $brand): JsonResponse
    {
        $models = $brand->models()->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'message' => 'Models retrieved successfully',
            'data' => $models,
        ]);
    }
}
