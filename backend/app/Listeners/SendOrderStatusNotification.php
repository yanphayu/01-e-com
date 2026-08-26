<?php

namespace App\Listeners;

use App\Events\OrderStatusChanged;
use App\Services\NotificationService;

class SendOrderStatusNotification
{
    public function __construct(
        protected NotificationService $notificationService,
    ) {}

    public function handle(OrderStatusChanged $event): void
    {
        $order = $event->order;

        $this->notificationService->sendOrderStatusNotification(
            $order->buyer_id,
            $order->id,
            $event->newStatus,
        );
    }
}
