<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Report::with(['user.profile', 'product.images']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $reports = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $reports,
        ]);
    }

    public function resolve(Report $report, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'sometimes|in:resolved,dismissed,pending',
        ]);

        $status = $validated['status'] ?? 'resolved';

        $report->update(['status' => $status]);

        return response()->json([
            'success' => true,
            'message' => 'Report updated.',
            'data' => $report,
        ]);
    }
}
