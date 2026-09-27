<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $placement = $request->query('placement');

        $ads = Ad::query()
            ->active()
            ->forPlacement(is_string($placement) ? $placement : null)
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get(['id', 'placement', 'headline', 'subtext', 'button_label', 'link_url', 'image']);

        return response()->json([
            'success' => true,
            'data' => $ads,
        ]);
    }
}
