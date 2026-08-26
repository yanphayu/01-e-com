<?php

namespace App\Repositories\Interfaces;

interface CouponRepositoryInterface extends BaseRepositoryInterface
{
    public function findByCode(string $code);
    public function getActiveCoupons(string $sellerId);
    public function validateCoupon(string $code, string $sellerId, float $orderAmount);
}
