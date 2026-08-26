<?php

namespace App\Repositories\Eloquent;

use App\Models\Order;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use Illuminate\Support\Facades\DB;

class OrderRepository extends BaseRepository implements OrderRepositoryInterface
{
    public function __construct(Order $model)
    {
        parent::__construct($model);
    }

    public function findByOrderNumber(string $orderNumber)
    {
        return $this->model->where('order_number', $orderNumber)->first();
    }

    public function getByBuyer(string $buyerId, ?string $status = null, int $perPage = 15)
    {
        $query = $this->model
            ->where('buyer_id', $buyerId)
            ->with(['seller', 'items', 'payment']);

        if ($status) {
            $query->where('status', $status);
        }

        return $query->latest()->paginate($perPage);
    }

    public function getBySeller(string $sellerId, ?string $status = null, int $perPage = 15)
    {
        $query = $this->model
            ->where('seller_id', $sellerId)
            ->with(['buyer', 'items', 'payment']);

        if ($status) {
            $query->where('status', $status);
        }

        return $query->latest()->paginate($perPage);
    }

    public function getByCourier(string $courierId, ?string $status = null, int $perPage = 15)
    {
        $query = $this->model
            ->where('courier_id', $courierId)
            ->with(['buyer', 'seller', 'items']);

        if ($status) {
            $query->where('status', $status);
        }

        return $query->latest()->paginate($perPage);
    }

    public function updateStatus(string $orderId, string $status)
    {
        $order = $this->findOrFail($orderId);

        $updateData = ['status' => $status];

        $timestamps = [
            'shipped' => 'shipped_at',
            'delivered' => 'delivered_at',
            'cancelled' => 'cancelled_at',
        ];

        if (isset($timestamps[$status])) {
            $updateData[$timestamps[$status]] = now();
        }

        $order->update($updateData);

        return $order->fresh();
    }

    public function cancel(string $orderId, ?string $reason = null)
    {
        $order = $this->findOrFail($orderId);
        $order->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancel_reason' => $reason,
        ]);

        return $order->fresh();
    }

    public function getDashboardStats(?string $sellerId = null): array
    {
        $query = $this->model->newQuery();

        if ($sellerId) {
            $query->where('seller_id', $sellerId);
        }

        $totalOrders = (clone $query)->count();
        $totalRevenue = (clone $query)
            ->where('status', 'completed')
            ->sum('total');

        $ordersByStatus = (clone $query)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return [
            'total_orders' => $totalOrders,
            'total_revenue' => $totalRevenue,
            'orders_by_status' => $ordersByStatus,
        ];
    }

    public function getRevenueReport(string $startDate, string $endDate, ?string $sellerId = null)
    {
        $query = $this->model
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate]);

        if ($sellerId) {
            $query->where('seller_id', $sellerId);
        }

        return $query
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(total) as revenue')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }
}