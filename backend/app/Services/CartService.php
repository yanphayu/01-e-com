<?php

namespace App\Services;

use App\Repositories\Interfaces\CartRepositoryInterface;
use App\Repositories\Interfaces\NotificationRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use InvalidArgumentException;

class CartService
{
    public function __construct(
        protected CartRepositoryInterface $cartRepository,
        protected ProductRepositoryInterface $productRepository,
        protected NotificationRepositoryInterface $notificationRepository,
    ) {}

    public function getCart(string $userId)
    {
        $cart = $this->cartRepository->getOrCreateForUser($userId);

        return $this->cartRepository->getCartWithItems($cart->id);
    }

    public function addItem(string $userId, string $productId, int $quantity = 1)
    {
        $product = $this->productRepository->find($productId);

        if (!$product || !$product->is_active || $product->is_suspended) {
            throw new InvalidArgumentException('Product is not available.');
        }

        if ($product->stock < $quantity) {
            throw new InvalidArgumentException(
                "Insufficient stock. Available: {$product->stock}"
            );
        }

        $cart = $this->cartRepository->getOrCreateForUser($userId);

        $existingItem = $cart->items->first(fn ($item) => $item->product_id === $productId);

        if ($existingItem) {
            $newQuantity = $existingItem->quantity + $quantity;

            if ($newQuantity > $product->stock) {
                throw new InvalidArgumentException(
                    "Cannot add more. Maximum available stock: {$product->stock}"
                );
            }

            $this->cartRepository->updateItemQuantity($existingItem->id, $newQuantity);
        } else {
            $this->cartRepository->addItem($cart->id, $productId, $quantity);
        }

        return $this->cartRepository->getCartWithItems($cart->id);
    }

    public function updateItem(string $userId, string $cartItemId, int $quantity)
    {
        $cart = $this->cartRepository->getOrCreateForUser($userId);

        $cartItem = $cart->items->first(fn ($item) => $item->id === $cartItemId);

        if (!$cartItem) {
            throw new InvalidArgumentException('Cart item not found.');
        }

        if ($quantity <= 0) {
            $this->cartRepository->removeItem($cartItemId);
            return $this->cartRepository->getCartWithItems($cart->id);
        }

        $product = $this->productRepository->find($cartItem->product_id);

        if ($quantity > $product->stock) {
            throw new InvalidArgumentException(
                "Insufficient stock. Available: {$product->stock}"
            );
        }

        $this->cartRepository->updateItemQuantity($cartItemId, $quantity);

        return $this->cartRepository->getCartWithItems($cart->id);
    }

    public function removeItem(string $userId, string $cartItemId)
    {
        $cart = $this->cartRepository->getOrCreateForUser($userId);

        $cartItem = $cart->items->first(fn ($item) => $item->id === $cartItemId);

        if (!$cartItem) {
            throw new InvalidArgumentException('Cart item not found.');
        }

        $this->cartRepository->removeItem($cartItemId);

        return $this->cartRepository->getCartWithItems($cart->id);
    }

    public function clearCart(string $userId)
    {
        $cart = $this->cartRepository->getOrCreateForUser($userId);

        $this->cartRepository->clearCart($cart->id);

        return $this->cartRepository->getCartWithItems($cart->id);
    }

    public function getCartTotal(string $userId): float
    {
        $cart = $this->cartRepository->getOrCreateForUser($userId);
        $cartWithItems = $this->cartRepository->getCartWithItems($cart->id);

        return $cartWithItems->getTotalAttribute();
    }
}
