<?php

namespace App\Services;

use App\Models\Review;
use App\Repositories\Interfaces\NotificationRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Repositories\Interfaces\ReviewRepositoryInterface;
use InvalidArgumentException;

class ReviewService
{
    public function __construct(
        protected ReviewRepositoryInterface $reviewRepository,
        protected ProductRepositoryInterface $productRepository,
        protected NotificationRepositoryInterface $notificationRepository,
    ) {}

    public function create(array $data): Review
    {
        $product = $this->productRepository->find($data['product_id']);

        if (!$product) {
            throw new InvalidArgumentException('Product not found.');
        }

        $existingReview = Review::where('user_id', $data['user_id'])
            ->where('product_id', $data['product_id'])
            ->whereNull('deleted_at')
            ->exists();

        if ($existingReview) {
            throw new InvalidArgumentException('You have already reviewed this product.');
        }

        $orderItem = \App\Models\OrderItem::where('product_id', $data['product_id'])
            ->whereHas('order', function ($q) use ($data) {
                $q->where('buyer_id', $data['user_id'])->where('status', 'delivered');
            })
            ->exists();

        if (!$orderItem) {
            throw new InvalidArgumentException('You must purchase this product before reviewing it.');
        }

        $review = $this->reviewRepository->create([
            'user_id' => $data['user_id'],
            'product_id' => $data['product_id'],
            'order_id' => $data['order_id'] ?? null,
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
            'image' => $data['image'] ?? null,
            'is_approved' => false,
        ]);

        $this->notificationRepository->createNotification(
            $product->seller->user_id ?? $product->seller_id,
            'New Review',
            "Your product {$product->name} received a new review.",
            'review',
            ['review_id' => $review->id, 'product_id' => $product->id]
        );

        return $review->load('user', 'product');
    }

    public function getByProduct(string $productId, int $perPage = 15)
    {
        return $this->reviewRepository->getByProduct($productId, $perPage);
    }

    public function getBySeller(string $sellerId, int $perPage = 15)
    {
        return $this->reviewRepository->getBySeller($sellerId, $perPage);
    }

    public function approve(string $reviewId): Review
    {
        $review = $this->reviewRepository->find($reviewId);

        if (!$review) {
            throw new InvalidArgumentException('Review not found.');
        }

        if ($review->is_approved) {
            throw new InvalidArgumentException('Review is already approved.');
        }

        $this->reviewRepository->approve($reviewId);

        $this->notificationRepository->createNotification(
            $review->user_id,
            'Review Approved',
            "Your review for product has been approved.",
            'review',
            ['review_id' => $review->id, 'product_id' => $review->product_id]
        );

        return $this->reviewRepository->find($reviewId);
    }

    public function disapprove(string $reviewId): Review
    {
        $review = $this->reviewRepository->find($reviewId);

        if (!$review) {
            throw new InvalidArgumentException('Review not found.');
        }

        if (!$review->is_approved) {
            throw new InvalidArgumentException('Review is already disapproved.');
        }

        $this->reviewRepository->disapprove($reviewId);

        $this->notificationRepository->createNotification(
            $review->user_id,
            'Review Disapproved',
            "Your review for product has been disapproved.",
            'review',
            ['review_id' => $review->id, 'product_id' => $review->product_id]
        );

        return $this->reviewRepository->find($reviewId);
    }

    public function getAverageRating(string $productId): float
    {
        return $this->reviewRepository->getAverageRating($productId);
    }

    public function delete(string $reviewId): bool
    {
        $review = $this->reviewRepository->find($reviewId);

        if (!$review) {
            throw new InvalidArgumentException('Review not found.');
        }

        $this->reviewRepository->delete($reviewId);

        $this->notificationRepository->createNotification(
            $review->product->seller->user_id ?? $review->product->seller_id,
            'Review Deleted',
            "A review for your product has been deleted.",
            'review',
            ['review_id' => $review->id, 'product_id' => $review->product_id]
        );

        return true;
    }
}
