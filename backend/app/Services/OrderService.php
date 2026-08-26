<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Repositories\Interfaces\CartRepositoryInterface;
use App\Repositories\Interfaces\NotificationRepositoryInterface;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Repositories\Interfaces\PaymentRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class OrderService
{
    public function __construct(
        protected OrderRepositoryInterface $orderRepository,
        protected CartRepositoryInterface $cartRepository,
        protected ProductRepositoryInterface $productRepository,
        protected PaymentRepositoryInterface $paymentRepository,
        protected NotificationRepositoryInterface $notificationRepository,
    ) {}

    public function create(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            $cart = $this->cartRepository->getOrCreateForUser($data['buyer_id']);
            $cartWithItems = $this->cartRepository->getCartWithItems($cart->id);

            if ($cartWithItems->items->isEmpty()) {
                throw new InvalidArgumentException('Cart is empty.');
            }

            $subtotal = 0;
            $orderItems = [];
            $sellerId = null;

            foreach ($cartWithItems->items as $item) {
                $product = $this->productRepository->find($item->product_id);

                if ($product->stock < $item->quantity) {
                    throw new InvalidArgumentException(
                        "Insufficient stock for product: {$product->name}. Available: {$product->stock}"
                    );
                }

                if ($sellerId === null) {
                    $sellerId = $product->seller_id;
                }

                $itemTotal = (float) $product->price * $item->quantity;
                $subtotal += $itemTotal;

                $orderItems[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'price' => $product->price,
                    'quantity' => $item->quantity,
                    'total' => $itemTotal,
                ];
            }

            $shippingFee = (float) ($data['shipping_fee'] ?? 0);
            $tax = (float) ($data['tax'] ?? 0);
            $discount = (float) ($data['discount'] ?? 0);
            $total = $subtotal + $shippingFee + $tax - $discount;

            $order = $this->orderRepository->create([
                'order_number' => 'ORD-' . time() . rand(1000, 9999),
                'buyer_id' => $data['buyer_id'],
                'seller_id' => $sellerId,
                'status' => 'pending',
                'subtotal' => $subtotal,
                'shipping_fee' => $shippingFee,
                'tax' => $tax,
                'discount' => $discount,
                'total' => $total,
                'payment_method' => $data['payment_method'] ?? null,
                'payment_status' => 'pending',
                'shipping_address' => $data['shipping_address'] ?? null,
                'shipping_city' => $data['shipping_city'] ?? null,
                'shipping_phone' => $data['shipping_phone'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($orderItems as $item) {
                OrderItem::create(array_merge($item, ['order_id' => $order->id]));
                $this->productRepository->decrementStock($item['product_id'], $item['quantity']);
            }

            $this->cartRepository->clearCart($cart->id);

            $this->notificationRepository->createNotification(
                $sellerId,
                'New Order',
                "You have a new order #{$order->order_number}.",
                'order',
                ['order_id' => $order->id]
            );

            return $this->orderRepository->find($order->id);
        });
    }

    public function getById(string $orderId): Order
    {
        return $this->orderRepository->findOrFail($orderId);
    }

    public function getByBuyer(string $buyerId, ?string $status = null, int $perPage = 15)
    {
        return $this->orderRepository->getByBuyer($buyerId, $status, $perPage);
    }

    public function getBySeller(string $sellerId, ?string $status = null, int $perPage = 15)
    {
        return $this->orderRepository->getBySeller($sellerId, $status, $perPage);
    }

    public function acceptOrder(string $orderId): Order
    {
        $order = $this->orderRepository->findOrFail($orderId);

        if ($order->status !== 'pending') {
            throw new InvalidArgumentException('Only pending orders can be accepted.');
        }

        $this->orderRepository->updateStatus($orderId, 'accepted');

        $this->notificationRepository->createNotification(
            $order->buyer_id,
            'Order Accepted',
            "Your order #{$order->order_number} has been accepted.",
            'order',
            ['order_id' => $order->id]
        );

        return $this->orderRepository->find($orderId);
    }

    public function rejectOrder(string $orderId): Order
    {
        $order = $this->orderRepository->findOrFail($orderId);

        if ($order->status !== 'pending') {
            throw new InvalidArgumentException('Only pending orders can be rejected.');
        }

        $this->orderRepository->updateStatus($orderId, 'rejected');

        $this->notificationRepository->createNotification(
            $order->buyer_id,
            'Order Rejected',
            "Your order #{$order->order_number} has been rejected.",
            'order',
            ['order_id' => $order->id]
        );

        return $this->orderRepository->find($orderId);
    }

    public function packOrder(string $orderId): Order
    {
        $order = $this->orderRepository->findOrFail($orderId);

        if ($order->status !== 'accepted') {
            throw new InvalidArgumentException('Only accepted orders can be packed.');
        }

        $this->orderRepository->updateStatus($orderId, 'packed');

        return $this->orderRepository->find($orderId);
    }

    public function shipOrder(string $orderId): Order
    {
        $order = $this->orderRepository->findOrFail($orderId);

        if ($order->status !== 'packed') {
            throw new InvalidArgumentException('Only packed orders can be shipped.');
        }

        $this->orderRepository->updateStatus($orderId, 'shipping');

        $this->notificationRepository->createNotification(
            $order->buyer_id,
            'Order Shipped',
            "Your order #{$order->order_number} has been shipped.",
            'order',
            ['order_id' => $order->id]
        );

        return $this->orderRepository->find($orderId);
    }

    public function deliverOrder(string $orderId): Order
    {
        $order = $this->orderRepository->findOrFail($orderId);

        if ($order->status !== 'shipping') {
            throw new InvalidArgumentException('Only shipping orders can be marked as delivered.');
        }

        $this->orderRepository->updateStatus($orderId, 'delivered');

        $this->notificationRepository->createNotification(
            $order->buyer_id,
            'Order Delivered',
            "Your order #{$order->order_number} has been delivered.",
            'order',
            ['order_id' => $order->id]
        );

        return $this->orderRepository->find($orderId);
    }

    public function cancelOrder(string $orderId, ?string $reason = null): Order
    {
        $order = $this->orderRepository->findOrFail($orderId);

        if (in_array($order->status, ['delivered', 'cancelled'])) {
            throw new InvalidArgumentException('Cannot cancel a delivered or already cancelled order.');
        }

        return DB::transaction(function () use ($orderId, $reason, $order) {
            $this->orderRepository->cancel($orderId, $reason);

            foreach ($order->items as $item) {
                $this->productRepository->decrementStock($item->product_id, -$item->quantity);
            }

            $this->notificationRepository->createNotification(
                $order->seller_id,
                'Order Cancelled',
                "Order #{$order->order_number} has been cancelled.",
                'order',
                ['order_id' => $order->id]
            );

            return $this->orderRepository->find($orderId);
        });
    }

    public function getDashboardStats(?string $sellerId = null): array
    {
        return $this->orderRepository->getDashboardStats($sellerId);
    }

    public function getRevenueReport(string $startDate, string $endDate, ?string $sellerId = null)
    {
        return $this->orderRepository->getRevenueReport($startDate, $endDate, $sellerId);
    }
}
