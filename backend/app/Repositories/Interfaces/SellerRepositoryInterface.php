<?php

namespace App\Repositories\Interfaces;

interface SellerRepositoryInterface extends BaseRepositoryInterface
{
    public function findByUserId(string $userId);
    public function findBySlug(string $slug);
    public function suspend(string $sellerId, ?string $reason = null);
    public function unsuspend(string $sellerId);
    public function getRevenue(string $sellerId, string $startDate, string $endDate);
    public function getTopSellers(int $limit = 10);
}
