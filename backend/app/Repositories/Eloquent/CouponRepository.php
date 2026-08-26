<?php

namespace App\Repositories\Eloquent;

use App\Models\Coupon;
use App\Repositories\Interfaces\CouponRepositoryInterface;

class CouponRepository extends BaseRepository implements CouponRepositoryInterface
{
    public function __construct(Coupon $model)
    {
        parent::__construct($model);
    }

    public function findByCode(string $code)
    {
        return $this->model->where('code', $code)->first();
    }

    public function getActiveCoupons(string $sellerId)
    {
        return $this->model
            ->where('seller_id', $sellerId)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->paginate(15);
    }

    public function getBySeller(string $sellerId, array $filters = [], int $perPage = 15)
    {
        $query = $this->model->where('seller_id', $sellerId);

        if (!empty($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function validateCoupon(string $code, string $sellerId, float $orderAmount)
    {
        $coupon = $this->model
            ->where('code', $code)
            ->where('seller_id', $sellerId)
            ->where('is_active', true)
            ->first();

        if (!$coupon) {
            throw new \InvalidArgumentException('Invalid coupon code.');
        }

        if ($coupon->starts_at && $coupon->starts_at->isFuture()) {
            throw new \InvalidArgumentException('Coupon is not yet active.');
        }

        if ($coupon->expires_at && $coupon->expires_at->isPast()) {
            throw new \InvalidArgumentException('Coupon has expired.');
        }

        if ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit) {
            throw new \InvalidArgumentException('Coupon usage limit reached.');
        }

        if ($orderAmount < $coupon->min_order_amount) {
            throw new \InvalidArgumentException("Minimum order amount is {$coupon->min_order_amount}.");
        }

        $discount = 0;
        if ($coupon->type === 'percentage') {
            $discount = ($orderAmount * $coupon->value) / 100;
            if ($coupon->max_discount && $discount > $coupon->max_discount) {
                $discount = $coupon->max_discount;
            }
        } else {
            $discount = min($coupon->value, $orderAmount);
        }

        return [
            'coupon' => $coupon,
            'discount' => round($discount, 2),
        ];
    }
}
