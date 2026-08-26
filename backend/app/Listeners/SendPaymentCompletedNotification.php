<?php

namespace App\Listeners;

use App\Events\PaymentCompleted;
use App\Services\NotificationService;

class SendPaymentCompletedNotification
{
    public function __construct(
        protected NotificationService $notificationService,
    ) {}

    public function handle(PaymentCompleted $event): void
    {
        $payment = $event->payment;

        $this->notificationService->sendPaymentNotification(
            $payment->user_id,
            $payment->order_id,
            $payment->status,
        );
    }
}
