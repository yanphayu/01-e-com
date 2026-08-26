<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SuspendUserRequest;
use App\Http\Requests\Seller\CreateSellerProfileRequest;
use App\Http\Requests\Seller\UpdateSellerProfileRequest;
use App\Services\SellerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SellerController extends Controller
{
    public function __construct(
        protected readonly SellerService $sellerService,
    ) {}

    public function store(CreateSellerProfileRequest $request): JsonResponse
    {
        try {
            $profile = $this->sellerService->createProfile(
                $request->user(),
                $request->validated(),
            );

            return response()->json([
                'message' => 'Seller profile created successfully.',
                'seller' => $profile,
            ], 201, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 15);

            $sellers = $this->sellerService->getAll($perPage);

            return response()->json($sellers, 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $profile = $this->sellerService->getProfile($id);

            return response()->json($profile, 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function showBySlug(string $slug): JsonResponse
    {
        try {
            $profile = $this->sellerService->getProfileBySlug($slug);

            return response()->json($profile, 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function update(UpdateSellerProfileRequest $request, string $id): JsonResponse
    {
        try {
            $profile = $this->sellerService->updateProfile($id, $request->validated());

            return response()->json([
                'message' => 'Seller profile updated successfully.',
                'seller' => $profile,
            ], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function suspend(string $id, SuspendUserRequest $request): JsonResponse
    {
        try {
            $profile = $this->sellerService->suspend($id, $request->input('reason'));

            return response()->json([
                'message' => 'Seller suspended successfully.',
                'seller' => $profile,
            ], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function unsuspend(string $id): JsonResponse
    {
        try {
            $profile = $this->sellerService->unsuspend($id);

            return response()->json([
                'message' => 'Seller unsuspended successfully.',
                'seller' => $profile,
            ], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function revenue(Request $request, string $id): JsonResponse
    {
        try {
            $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
            $endDate = $request->input('end_date', now()->toDateString());

            $revenue = $this->sellerService->getRevenue($id, $startDate, $endDate);

            return response()->json($revenue, 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }
    }
}
