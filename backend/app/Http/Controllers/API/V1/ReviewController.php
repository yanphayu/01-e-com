<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateReviewRequest;
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
            $review = $this->reviewService->create($request->user()->id, $request->validated());
            return response()->json($review, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function byProduct(string $productId, Request $request): JsonResponse
    {
        try {
            $reviews = $this->reviewService->getByProduct($productId, $request->query());
            return response()->json($reviews);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    public function bySeller(string $sellerId, Request $request): JsonResponse
    {
        try {
            $reviews = $this->reviewService->getBySeller($sellerId, $request->query());
            return response()->json($reviews);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    public function approve(string $id): JsonResponse
    {
        try {
            $review = $this->reviewService->approve($id);
            return response()->json($review);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function disapprove(string $id): JsonResponse
    {
        try {
            $review = $this->reviewService->disapprove($id);
            return response()->json($review);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
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
