<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateCouponRequest;
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
            $coupons = $this->couponRepository->getBySeller($request->user()->id, $request->query());
            return response()->json($coupons);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function store(CreateCouponRequest $request): JsonResponse
    {
        try {
            $coupon = $this->couponRepository->create($request->user()->id, $request->validated());
            return response()->json($coupon, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function update(CreateCouponRequest $request, string $id): JsonResponse
    {
        try {
            $coupon = $this->couponRepository->update($id, $request->user()->id, $request->validated());
            return response()->json($coupon);
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
            ]);
            $coupon = $this->couponRepository->validateCode(
                $request->input('code'),
                $request->input('cart_total'),
                $request->user()->id
            );
            return response()->json($coupon);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
