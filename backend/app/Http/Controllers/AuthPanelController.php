<?php

namespace App\Http\Controllers;

use App\Support\AuthPanelContent;
use Illuminate\Http\JsonResponse;

class AuthPanelController extends Controller
{
    public function show(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => AuthPanelContent::resolve(),
        ]);
    }
}
