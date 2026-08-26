<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Buyer\CreateReviewRequest;
use App\Services\ReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function __construct(
        private readonly ReviewService $reviewService
    ) {}

    public function store(CreateReviewRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $data['user_id'] = $request->user()->id;
            $review = $this->reviewService->create($data);
            return response()->json([
                'message' => 'Review created successfully.',
                'review' => $review,
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function byProduct(string $productId, Request $request): JsonResponse
    {
        try {
            $perPage = (int) $request->query('per_page', 15);
            $reviews = $this->reviewService->getByProduct($productId, $perPage);
            return response()->json($reviews);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    public function bySeller(string $sellerId, Request $request): JsonResponse
    {
        try {
            $perPage = (int) $request->query('per_page', 15);
            $reviews = $this->reviewService->getBySeller($sellerId, $perPage);
            return response()->json($reviews);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    public function approve(string $id): JsonResponse
    {
        try {
            $review = $this->reviewService->approve($id);
            return response()->json([
                'message' => 'Review approved successfully.',
                'review' => $review,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function disapprove(string $id): JsonResponse
    {
        try {
            $review = $this->reviewService->disapprove($id);
            return response()->json([
                'message' => 'Review disapproved successfully.',
                'review' => $review,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $this->reviewService->delete($id);
            return response()->json(['message' => 'Review deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
