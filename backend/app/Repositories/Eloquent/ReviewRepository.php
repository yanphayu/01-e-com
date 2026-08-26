<?php

namespace App\Repositories\Eloquent;

use App\Models\Review;
use App\Repositories\Interfaces\ReviewRepositoryInterface;
use Illuminate\Support\Facades\DB;

class ReviewRepository extends BaseRepository implements ReviewRepositoryInterface
{
    public function __construct(Review $model)
    {
        parent::__construct($model);
    }

    public function getByProduct(string $productId, int $perPage = 15)
    {
        return $this->model
            ->where('product_id', $productId)
            ->with('user')
            ->latest()
            ->paginate($perPage);
    }

    public function getBySeller(string $sellerId, int $perPage = 15)
    {
        return $this->model
            ->join('products', 'reviews.product_id', '=', 'products.id')
            ->where('products.seller_id', $sellerId)
            ->with('user')
            ->select('reviews.*')
            ->latest()
            ->paginate($perPage);
    }

    public function approve(string $reviewId)
    {
        $review = $this->findOrFail($reviewId);
        $review->update(['is_approved' => true]);

        return $review->fresh();
    }

    public function disapprove(string $reviewId)
    {
        $review = $this->findOrFail($reviewId);
        $review->update(['is_approved' => false]);

        return $review->fresh();
    }

    public function getAverageRating(string $productId): float
    {
        $result = $this->model
            ->where('product_id', $productId)
            ->where('is_approved', true)
            ->avg('rating');

        return round((float) $result, 1);
    }
}