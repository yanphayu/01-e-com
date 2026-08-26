<?php

namespace App\Services;

use App\Repositories\Interfaces\CouponRepositoryInterface;
use Illuminate\Support\Facades\DB;

class CouponService
{
    public function __construct(
        protected CouponRepositoryInterface $couponRepository,
    ) {}

    public function create(array $data)
    {
        return $this->couponRepository->create($data);
    }

    public function update(string $id, array $data)
    {
        return $this->couponRepository->update($id, $data);
    }

    public function delete(string $id)
    {
        return $this->couponRepository->delete($id);
    }

    public function getBySeller(string $sellerId, int $perPage = 15)
    {
        return $this->couponRepository->getActiveCoupons($sellerId);
    }

    public function validate(string $code, string $sellerId, float $cartTotal)
    {
        return $this->couponRepository->validateCoupon($code, $sellerId, $cartTotal);
    }
}
