<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function view(User $user, Order $order): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($order->buyer_id === $user->id) {
            return true;
        }

        if ($user->sellerProfile && $order->seller_id === $user->sellerProfile->id) {
            return true;
        }

        return false;
    }

    public function update(User $user, Order $order): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->sellerProfile && $order->seller_id === $user->sellerProfile->id) {
            return true;
        }

        return false;
    }

    public function cancel(User $user, Order $order): bool
    {
        return $order->buyer_id === $user->id
            && in_array($order->status, ['pending', 'accepted']);
    }
}
