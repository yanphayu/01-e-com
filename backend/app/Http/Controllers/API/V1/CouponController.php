<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Seller\CreateCouponRequest;
use App\Repositories\Interfaces\CouponRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function __construct(
        private readonly CouponRepositoryInterface $couponRepository
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $sellerProfile = $user->sellerProfile;

            if (!$sellerProfile) {
                return response()->json(['message' => 'Seller profile not found.'], 404);
            }

            $coupons = $this->couponRepository->getActiveCoupons($sellerProfile->id);
            return response()->json($coupons);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function store(CreateCouponRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            $sellerProfile = $user->sellerProfile;

            if (!$sellerProfile) {
                return response()->json(['message' => 'Seller profile not found.'], 404);
            }

            $coupon = $this->couponRepository->create(array_merge(
                $request->validated(),
                ['seller_id' => $sellerProfile->id]
            ));
            return response()->json([
                'message' => 'Coupon created successfully.',
                'coupon' => $coupon,
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function update(CreateCouponRequest $request, string $id): JsonResponse
    {
        try {
            $coupon = $this->couponRepository->update($id, $request->validated());
            return response()->json([
                'message' => 'Coupon updated successfully.',
                'coupon' => $coupon,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $this->couponRepository->delete($id);
            return response()->json(['message' => 'Coupon deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function validate(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'code' => 'required|string',
                'cart_total' => 'required|numeric|min:0',
                'seller_id' => 'required|exists:seller_profiles,id',
            ]);
            $result = $this->couponRepository->validateCoupon(
                $request->input('code'),
                $request->input('seller_id'),
                (float) $request->input('cart_total')
            );
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
