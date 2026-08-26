<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Product $product): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('seller');
    }

    public function update(User $user, Product $product): bool
    {
        return $user->hasRole('admin')
            || ($user->sellerProfile && $product->seller_id === $user->sellerProfile->id);
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->hasRole('admin')
            || ($user->sellerProfile && $product->seller_id === $user->sellerProfile->id);
    }

    public function suspend(User $user): bool
    {
        return $user->hasRole('admin');
    }
}
