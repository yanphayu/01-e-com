<?php

namespace App\Services;

use App\Models\Product;
use App\Repositories\Interfaces\NotificationRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use Illuminate\Support\Facades\DB;

class ProductService
{
    public function __construct(
        protected ProductRepositoryInterface $productRepository,
        protected NotificationRepositoryInterface $notificationRepository,
    ) {}

    public function create(array $data): Product
    {
        return DB::transaction(function () use ($data) {
            $product = $this->productRepository->create($data);

            if (!empty($data['images'])) {
                foreach ($data['images'] as $image) {
                    $product->images()->create($image);
                }
            }

            return $product->load('images', 'category', 'seller');
        });
    }

    public function update(string $id, array $data): Product
    {
        $this->productRepository->update($id, $data);

        $product = $this->productRepository->findOrFail($id);

        if (!empty($data['images'])) {
            $product->images()->delete();
            foreach ($data['images'] as $image) {
                $product->images()->create($image);
            }
        }

        return $product->load('images', 'category', 'seller');
    }

    public function getById(string $id): Product
    {
        $product = $this->productRepository->findOrFail($id);

        $this->productRepository->incrementViewCount($id);

        return $product->load('images', 'category', 'seller.user', 'reviews');
    }

    public function getBySlug(string $slug): Product
    {
        $product = $this->productRepository->findBySlug($slug);

        if (!$product) {
            throw new \Exception('Product not found.');
        }

        $this->productRepository->incrementViewCount($product->id);

        return $product->load('images', 'category', 'seller.user', 'reviews');
    }

    public function search(array $filters, int $perPage = 15)
    {
        return $this->productRepository->searchProducts($filters, $perPage);
    }

    public function getFeatured(int $limit = 10)
    {
        return $this->productRepository->getFeaturedProducts($limit);
    }

    public function getRelated(string $productId, int $limit = 8)
    {
        return $this->productRepository->getRelatedProducts($productId, $limit);
    }

    public function getBySeller(string $sellerId, int $perPage = 15)
    {
        return $this->productRepository->getBySeller($sellerId, $perPage);
    }

    public function delete(string $id): void
    {
        $this->productRepository->delete($id);
    }

    public function suspend(string $id): Product
    {
        return $this->productRepository->suspend($id);
    }

    public function activate(string $id): Product
    {
        return $this->productRepository->activate($id);
    }
}
