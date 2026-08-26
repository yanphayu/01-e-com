<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Services\NotificationService;

class SendOrderCreatedNotification
{
    public function __construct(
        protected NotificationService $notificationService,
    ) {}

    public function handle(OrderCreated $event): void
    {
        $order = $event->order;
        $seller = $order->seller->user;

        $this->notificationService->sendOrderStatusNotification(
            $seller->id,
            $order->id,
            'pending',
        );
    }
}
