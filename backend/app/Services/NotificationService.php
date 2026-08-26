<?php

namespace App\Services;

use App\Repositories\Interfaces\NotificationRepositoryInterface;

class NotificationService
{
    public function __construct(
        protected NotificationRepositoryInterface $notificationRepository,
    ) {}

    public function getUserNotifications(string $userId, int $perPage = 20, ?string $type = null)
    {
        return $this->notificationRepository->getForUser($userId, $perPage, $type);
    }

    public function markAsRead(string $notificationId)
    {
        return $this->notificationRepository->markAsRead($notificationId);
    }

    public function markAllAsRead(string $userId)
    {
        return $this->notificationRepository->markAllAsRead($userId);
    }

    public function getUnreadCount(string $userId): int
    {
        return $this->notificationRepository->getUnreadCount($userId);
    }

    public function sendOrderStatusNotification(string $userId, string $orderId, string $status): void
    {
        $titles = [
            'pending' => 'Order Pending',
            'accepted' => 'Order Accepted',
            'rejected' => 'Order Rejected',
            'packed' => 'Order Packed',
            'shipping' => 'Order Shipped',
            'delivered' => 'Order Delivered',
            'cancelled' => 'Order Cancelled',
        ];

        $title = $titles[$status] ?? 'Order Status Updated';
        $message = "Your order status has been updated to: {$status}.";

        $this->notificationRepository->createNotification(
            $userId,
            $title,
            $message,
            'order',
            ['order_id' => $orderId, 'status' => $status]
        );
    }

    public function sendPaymentNotification(string $userId, string $orderId, string $status): void
    {
        $titles = [
            'pending' => 'Payment Pending',
            'paid' => 'Payment Successful',
            'failed' => 'Payment Failed',
            'refunded' => 'Payment Refunded',
        ];

        $title = $titles[$status] ?? 'Payment Status Updated';
        $message = "Your payment status for the order has been updated to: {$status}.";

        $this->notificationRepository->createNotification(
            $userId,
            $title,
            $message,
            'payment',
            ['order_id' => $orderId, 'status' => $status]
        );
    }

    public function sendDeliveryNotification(string $userId, string $deliveryId, string $status): void
    {
        $titles = [
            'assigned' => 'Delivery Assigned',
            'accepted' => 'Delivery Accepted',
            'picked_up' => 'Order Picked Up',
            'in_transit' => 'Delivery In Transit',
            'delivered' => 'Delivery Completed',
            'failed' => 'Delivery Failed',
        ];

        $title = $titles[$status] ?? 'Delivery Status Updated';
        $message = "Your delivery status has been updated to: {$status}.";

        $this->notificationRepository->createNotification(
            $userId,
            $title,
            $message,
            'delivery',
            ['delivery_id' => $deliveryId, 'status' => $status]
        );
    }
}
