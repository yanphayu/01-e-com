<?php

namespace App\Repositories\Eloquent;

use App\Models\Product;
use App\Repositories\Interfaces\ProductRepositoryInterface;

class ProductRepository extends BaseRepository implements ProductRepositoryInterface
{
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    public function findBySlug(string $slug)
    {
        return $this->model->where('slug', $slug)->first();
    }

    public function searchProducts(array $filters, int $perPage = 15)
    {
        $query = $this->model->with(['category', 'seller']);

        if (! empty($filters['keyword'])) {
            $keyword = $filters['keyword'];
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'LIKE', "%{$keyword}%")
                    ->orWhere('description', 'LIKE', "%{$keyword}%");
            });
        }

        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (! empty($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (! empty($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        if (! empty($filters['condition'])) {
            $query->where('condition', $filters['condition']);
        }

        if (! empty($filters['seller_id'])) {
            $query->where('seller_id', $filters['seller_id']);
        }

        return $query->paginate($perPage);
    }

    public function getFeaturedProducts(int $limit = 10)
    {
        return $this->model
            ->where('status', 'active')
            ->where('is_featured', true)
            ->with(['category', 'seller', 'images'])
            ->take($limit)
            ->get();
    }

    public function getRelatedProducts(string $productId, int $limit = 8)
    {
        $product = $this->findOrFail($productId);

        return $this->model
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $productId)
            ->where('status', 'active')
            ->take($limit)
            ->get();
    }

    public function getBySeller(string $sellerId, int $perPage = 15)
    {
        return $this->model
            ->where('seller_id', $sellerId)
            ->with(['category', 'images'])
            ->paginate($perPage);
    }

    public function getByCategory(string $categoryId, int $perPage = 15)
    {
        return $this->model
            ->where('category_id', $categoryId)
            ->where('status', 'active')
            ->with(['seller', 'images'])
            ->paginate($perPage);
    }

    public function incrementViewCount(string $productId)
    {
        $product = $this->findOrFail($productId);
        $product->increment('view_count');

        return $product->fresh();
    }

    public function decrementStock(string $productId, int $quantity)
    {
        $product = $this->findOrFail($productId);
        $product->decrement('stock', $quantity);

        return $product->fresh();
    }

    public function suspend(string $productId)
    {
        $product = $this->findOrFail($productId);
        $product->update(['status' => 'suspended']);

        return $product->fresh();
    }

    public function activate(string $productId)
    {
        $product = $this->findOrFail($productId);
        $product->update(['status' => 'active']);

        return $product->fresh();
    }
}