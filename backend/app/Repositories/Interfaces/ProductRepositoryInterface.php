<?php

namespace App\Repositories\Interfaces;

interface ProductRepositoryInterface extends BaseRepositoryInterface
{
    public function findBySlug(string $slug);
    public function searchProducts(array $filters, int $perPage = 15);
    public function getFeaturedProducts(int $limit = 10);
    public function getRelatedProducts(string $productId, int $limit = 8);
    public function getBySeller(string $sellerId, int $perPage = 15);
    public function getByCategory(string $categoryId, int $perPage = 15);
    public function incrementViewCount(string $productId);
    public function decrementStock(string $productId, int $quantity);
    public function suspend(string $productId);
    public function activate(string $productId);
}
