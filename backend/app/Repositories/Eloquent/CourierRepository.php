<?php

namespace App\Repositories\Eloquent;

use App\Models\Courier;
use App\Repositories\Interfaces\CourierRepositoryInterface;
use Illuminate\Support\Facades\DB;

class CourierRepository extends BaseRepository implements CourierRepositoryInterface
{
    public function __construct(Courier $model)
    {
        parent::__construct($model);
    }

    public function findByUserId(string $userId)
    {
        return $this->model->where('user_id', $userId)->first();
    }

    public function setOnline(string $courierId)
    {
        $courier = $this->findOrFail($courierId);
        $courier->update(['is_online' => true]);

        return $courier->fresh();
    }

    public function setOffline(string $courierId)
    {
        $courier = $this->findOrFail($courierId);
        $courier->update(['is_online' => false]);

        return $courier->fresh();
    }

    public function getAvailableCouriers()
    {
        return $this->model
            ->where('is_online', true)
            ->where('is_available', true)
            ->where('status', 'active')
            ->get();
    }

    public function getDeliveries(string $courierId, ?string $status = null, int $perPage = 15)
    {
        $query = $this->model
            ->find($courierId)
            ->deliveries()
            ->with('order');

        if ($status) {
            $query->where('status', $status);
        }

        return $query->latest()->paginate($perPage);
    }

    public function getEarnings(string $courierId, string $startDate, string $endDate)
    {
        return $this->model->find($courierId)
            ->deliveries()
            ->where('status', 'delivered')
            ->whereBetween('delivered_at', [$startDate, $endDate])
            ->sum('fee');
    }
}