<?php

namespace App\Services;

use App\Models\Delivery;
use App\Repositories\Interfaces\CourierRepositoryInterface;
use App\Repositories\Interfaces\NotificationRepositoryInterface;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class DeliveryService
{
    public function __construct(
        protected CourierRepositoryInterface $courierRepository,
        protected OrderRepositoryInterface $orderRepository,
        protected NotificationRepositoryInterface $notificationRepository,
    ) {}

    public function assignDelivery(string $orderId, string $courierId): Delivery
    {
        $order = $this->orderRepository->findOrFail($orderId);

        if ($order->status !== 'packed') {
            throw new InvalidArgumentException('Only packed orders can be assigned for delivery.');
        }

        $existingDelivery = Delivery::where('order_id', $orderId)->first();

        if ($existingDelivery) {
            throw new InvalidArgumentException('Delivery already assigned for this order.');
        }

        $courier = \App\Models\Courier::find($courierId);
        if (!$courier) {
            throw new InvalidArgumentException('Courier not found.');
        }

        $sellerProfile = \App\Models\SellerProfile::find($order->seller_id);

        $delivery = Delivery::create([
            'order_id' => $orderId,
            'courier_id' => $courierId,
            'status' => 'assigned',
            'fee' => $order->shipping_fee,
            'pickup_address' => $sellerProfile?->address ?? null,
            'delivery_address' => $order->shipping_address,
        ]);

        $this->orderRepository->update($orderId, ['courier_id' => $courierId]);

        $this->notificationRepository->createNotification(
            $courier->user_id,
            'Delivery Assigned',
            "You have a new delivery assignment for order #{$order->order_number}.",
            'delivery',
            ['delivery_id' => $delivery->id, 'order_id' => $orderId]
        );

        $this->notificationRepository->createNotification(
            $order->buyer_id,
            'Delivery Assigned',
            "A courier has been assigned for your order #{$order->order_number}.",
            'delivery',
            ['delivery_id' => $delivery->id, 'order_id' => $orderId]
        );

        return $delivery->load('order', 'courier');
    }

    public function acceptDelivery(string $deliveryId): Delivery
    {
        $delivery = Delivery::findOrFail($deliveryId);

        if ($delivery->status !== 'assigned') {
            throw new InvalidArgumentException('Only assigned deliveries can be accepted.');
        }

        $delivery->update(['status' => 'accepted']);

        $this->notificationRepository->createNotification(
            $delivery->order->buyer_id,
            'Delivery Accepted',
            "Your delivery has been accepted by the courier.",
            'delivery',
            ['delivery_id' => $delivery->id]
        );

        return $delivery->fresh()->load('order', 'courier');
    }

    public function pickupOrder(string $deliveryId): Delivery
    {
        $delivery = Delivery::findOrFail($deliveryId);

        if (!in_array($delivery->status, ['assigned', 'accepted'])) {
            throw new InvalidArgumentException('Delivery must be accepted before pickup.');
        }

        $delivery->update([
            'status' => 'picked_up',
            'picked_up_at' => now(),
        ]);

        $this->orderRepository->updateStatus($delivery->order_id, 'shipping');

        $this->notificationRepository->createNotification(
            $delivery->order->buyer_id,
            'Order Picked Up',
            "Your order #{$delivery->order->order_number} has been picked up by the courier.",
            'delivery',
            ['delivery_id' => $delivery->id]
        );

        return $delivery->fresh()->load('order', 'courier');
    }

    public function inTransit(string $deliveryId): Delivery
    {
        $delivery = Delivery::findOrFail($deliveryId);

        if ($delivery->status !== 'picked_up') {
            throw new InvalidArgumentException('Delivery must be picked up before marking as in transit.');
        }

        $delivery->update(['status' => 'in_transit']);

        $this->notificationRepository->createNotification(
            $delivery->order->buyer_id,
            'Delivery In Transit',
            "Your order #{$delivery->order->order_number} is on its way.",
            'delivery',
            ['delivery_id' => $delivery->id]
        );

        return $delivery->fresh()->load('order', 'courier');
    }

    public function completeDelivery(string $deliveryId, ?string $proofImage = null): Delivery
    {
        $delivery = Delivery::findOrFail($deliveryId);

        if (!in_array($delivery->status, ['picked_up', 'in_transit'])) {
            throw new InvalidArgumentException('Delivery cannot be completed from current status.');
        }

        $delivery->update([
            'status' => 'delivered',
            'delivered_at' => now(),
            'proof_image' => $proofImage,
        ]);

        $this->orderRepository->updateStatus($delivery->order_id, 'delivered');

        $courier = $delivery->courier;
        if ($courier) {
            $courier->increment('total_deliveries');
            $courier->increment('total_earnings', $delivery->fee);
        }

        $this->notificationRepository->createNotification(
            $delivery->order->buyer_id,
            'Delivery Completed',
            "Your order #{$delivery->order->order_number} has been delivered successfully.",
            'delivery',
            ['delivery_id' => $delivery->id]
        );

        return $delivery->fresh()->load('order', 'courier');
    }

    public function failDelivery(string $deliveryId, ?string $reason = null): Delivery
    {
        $delivery = Delivery::findOrFail($deliveryId);

        if (in_array($delivery->status, ['delivered', 'failed'])) {
            throw new InvalidArgumentException('Cannot mark a completed or already failed delivery as failed.');
        }

        $delivery->update([
            'status' => 'failed',
            'notes' => $reason,
        ]);

        $this->notificationRepository->createNotification(
            $delivery->order->buyer_id,
            'Delivery Failed',
            "Delivery for order #{$delivery->order->order_number} has failed. " . ($reason ?: ''),
            'delivery',
            ['delivery_id' => $delivery->id, 'reason' => $reason]
        );

        $this->notificationRepository->createNotification(
            $delivery->order->seller_id,
            'Delivery Failed',
            "Delivery for order #{$delivery->order->order_number} has failed.",
            'delivery',
            ['delivery_id' => $delivery->id, 'reason' => $reason]
        );

        return $delivery->fresh()->load('order', 'courier');
    }

    public function getDeliveries(string $courierId, ?string $status = null, int $perPage = 15)
    {
        return $this->courierRepository->getDeliveries($courierId, $status, $perPage);
    }

    public function getEarnings(string $courierId, string $startDate, string $endDate): float
    {
        return (float) $this->courierRepository->getEarnings($courierId, $startDate, $endDate);
    }
}
