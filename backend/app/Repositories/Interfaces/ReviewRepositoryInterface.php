<?php

namespace App\Repositories\Interfaces;

interface ReviewRepositoryInterface extends BaseRepositoryInterface
{
    public function getByProduct(string $productId, int $perPage = 15);
    public function getBySeller(string $sellerId, int $perPage = 15);
    public function approve(string $reviewId);
    public function disapprove(string $reviewId);
    public function getAverageRating(string $productId): float;
}
