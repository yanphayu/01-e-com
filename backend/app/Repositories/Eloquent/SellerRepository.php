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
        return $this->model->where('slug', $slug)->first();
    }

    public function suspend(string $sellerId, ?string $reason = null)
    {
        $seller = $this->findOrFail($sellerId);
        $seller->update([
            'status' => 'suspended',
            'suspension_reason' => $reason,
            'suspended_at' => now(),
        ]);

        return $seller->fresh();
    }

    public function unsuspend(string $sellerId)
    {
        $seller = $this->findOrFail($sellerId);
        $seller->update([
            'status' => 'active',
            'suspension_reason' => null,
            'suspended_at' => null,
        ]);

        return $seller->fresh();
    }

    public function getRevenue(string $sellerId, string $startDate, string $endDate)
    {
        return $this->model->find($sellerId)
            ->orders()
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total');
    }

    public function getTopSellers(int $limit = 10)
    {
        return $this->model
            ->where('status', 'active')
            ->withCount(['orders as total_orders' => function ($query) {
                $query->where('status', 'completed');
            }])
            ->withSum(['orders as total_revenue' => function ($query) {
                $query->where('status', 'completed');
            }])
            ->orderByDesc('total_revenue')
            ->take($limit)
            ->get();
    }
}