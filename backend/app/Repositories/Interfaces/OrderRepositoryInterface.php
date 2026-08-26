<?php

namespace App\Repositories\Interfaces;

interface OrderRepositoryInterface extends BaseRepositoryInterface
{
    public function findByOrderNumber(string $orderNumber);
    public function getByBuyer(string $buyerId, ?string $status = null, int $perPage = 15);
    public function getBySeller(string $sellerId, ?string $status = null, int $perPage = 15);
    public function getByCourier(string $courierId, ?string $status = null, int $perPage = 15);
    public function updateStatus(string $orderId, string $status);
    public function cancel(string $orderId, ?string $reason = null);
    public function getDashboardStats(?string $sellerId = null): array;
    public function getRevenueReport(string $startDate, string $endDate, ?string $sellerId = null);
}
