<?php

namespace App\Repositories\Eloquent;

use App\Models\SellerProfile;
use App\Repositories\Interfaces\SellerRepositoryInterface;
use Illuminate\Support\Facades\DB;

class SellerRepository extends BaseRepository implements SellerRepositoryInterface
{
    public function __construct(SellerProfile $model)
    {
        parent::__construct($model);
    }

    public function findByUserId(string $userId)
    {
        return $this->model->where('user_id', $userId)->first();
    }

    public function findBySlug(string $slug)
    {
        return $this->model->where('store_slug', $slug)->first();
    }

    public function suspend(string $sellerId, ?string $reason = null)
    {
        $seller = $this->findOrFail($sellerId);
        $seller->update([
            'is_suspended' => true,
            'suspension_reason' => $reason,
            'suspended_at' => now(),
        ]);

        return $seller->fresh();
    }

    public function unsuspend(string $sellerId)
    {
        $seller = $this->findOrFail($sellerId);
        $seller->update([
            'is_suspended' => false,
            'suspension_reason' => null,
            'suspended_at' => null,
        ]);

        return $seller->fresh();
    }

    public function getRevenue(string $sellerId, string $startDate, string $endDate)
    {
        return $this->model->find($sellerId)
            ->orders()
            ->where('status', 'delivered')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total');
    }

    public function getTopSellers(int $limit = 10)
    {
        return $this->model
            ->where('is_suspended', false)
            ->withCount(['orders as total_orders' => function ($query) {
                $query->where('status', 'delivered');
            }])
            ->withSum(['orders as total_revenue' => function ($query) {
                $query->where('status', 'delivered');
            }])
            ->orderByDesc('total_revenue')
            ->take($limit)
            ->get();
    }
}
