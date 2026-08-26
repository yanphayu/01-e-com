<?php

namespace App\Repositories\Interfaces;

interface CartRepositoryInterface extends BaseRepositoryInterface
{
    public function getOrCreateForUser(string $userId);
    public function addItem(string $cartId, string $productId, int $quantity = 1);
    public function updateItemQuantity(string $cartItemId, int $quantity);
    public function removeItem(string $cartItemId);
    public function clearCart(string $cartId);
    public function getCartWithItems(string $cartId);
}
