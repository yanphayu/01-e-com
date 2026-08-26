<?php

namespace App\Repositories\Interfaces;

interface CourierRepositoryInterface extends BaseRepositoryInterface
{
    public function findByUserId(string $userId);
    public function setOnline(string $courierId);
    public function setOffline(string $courierId);
    public function getAvailableCouriers();
    public function getDeliveries(string $courierId, ?string $status = null, int $perPage = 15);
    public function getEarnings(string $courierId, string $startDate, string $endDate);
}
