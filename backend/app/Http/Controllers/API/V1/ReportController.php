<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'reporter_id' => 'required|exists:users,id',
                'reportable_type' => 'required|string',
                'reportable_id' => 'required|integer',
                'reason' => 'required|string|max:1000',
            ]);
            $report = Report::create($validated);
            return response()->json($report, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $reports = Report::query()
                ->with('reporter')
                ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
                ->latest()
                ->paginate($request->query('per_page', 15));
            return response()->json($reports);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'status' => 'required|in:pending,reviewed,resolved,dismissed',
                'admin_notes' => 'nullable|string|max:2000',
            ]);
            $report = Report::findOrFail($id);
            $report->update($validated);
            return response()->json($report);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $report = Report::findOrFail($id);
            $report->delete();
            return response()->json(['message' => 'Report deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
