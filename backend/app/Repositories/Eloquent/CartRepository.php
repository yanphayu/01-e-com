<?php

namespace App\Repositories\Eloquent;

use App\Models\Cart;
use App\Models\CartItem;
use App\Repositories\Interfaces\CartRepositoryInterface;

class CartRepository extends BaseRepository implements CartRepositoryInterface
{
    public function __construct(Cart $model)
    {
        parent::__construct($model);
    }

    public function getOrCreateForUser(string $userId)
    {
        return $this->model->firstOrCreate(['user_id' => $userId]);
    }

    public function addItem(string $cartId, string $productId, int $quantity = 1)
    {
        $cartItem = $this->model->find($cartId)
            ->items()
            ->where('product_id', $productId)
            ->first();

        if ($cartItem) {
            $cartItem->increment('quantity', $quantity);
        } else {
            $cartItem = CartItem::create([
                'cart_id' => $cartId,
                'product_id' => $productId,
                'quantity' => $quantity,
            ]);
        }

        return $cartItem->fresh('product');
    }

    public function updateItemQuantity(string $cartItemId, int $quantity)
    {
        $cartItem = CartItem::findOrFail($cartItemId);
        $cartItem->update(['quantity' => $quantity]);

        return $cartItem->fresh('product');
    }

    public function removeItem(string $cartItemId)
    {
        $cartItem = CartItem::findOrFail($cartItemId);

        return $cartItem->delete();
    }

    public function clearCart(string $cartId)
    {
        $cart = $this->findOrFail($cartId);
        $cart->items()->delete();

        return true;
    }

    public function getCartWithItems(string $cartId)
    {
        return $this->model->with('items.product')->find($cartId);
    }
}