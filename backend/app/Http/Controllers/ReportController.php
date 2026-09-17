<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Report;
use App\Models\User;
use App\Notifications\ProductReported;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function store(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:255',
            'details' => 'nullable|string|max:2000',
        ]);

        if ($product->user_id === $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot report your own product.',
            ], 422);
        }

        $existing = Report::where('user_id', $request->user()->id)
            ->where('product_id', $product->id)
            ->where('status', 'pending')
            ->exists();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'You have already reported this product.',
            ], 422);
        }

        $report = Report::create([
            'user_id' => $request->user()->id,
            'product_id' => $product->id,
            'reason' => $validated['reason'],
            'details' => $validated['details'] ?? null,
        ]);

        $report->load('user.profile', 'product');

        User::query()->where('is_admin', true)->each(function (User $admin) use ($report) {
            try {
                $admin->notify(new ProductReported($report));
            } catch (\Exception $e) {
                // Broadcast may fail if Reverb is not running — notification is still saved to DB
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Product reported. Our team will review it shortly.',
            'data' => $report,
        ], 201);
    }
}
