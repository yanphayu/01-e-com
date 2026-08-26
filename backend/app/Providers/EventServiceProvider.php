<?php

namespace App\Providers;

use App\Events\OrderCreated;
use App\Events\OrderStatusChanged;
use App\Events\PaymentCompleted;
use App\Events\ProductCreated;
use App\Listeners\SendOrderCreatedNotification;
use App\Listeners\SendOrderStatusNotification;
use App\Listeners\SendPaymentCompletedNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        OrderCreated::class => [
            SendOrderCreatedNotification::class,
        ],
        OrderStatusChanged::class => [
            SendOrderStatusNotification::class,
        ],
        PaymentCompleted::class => [
            SendPaymentCompletedNotification::class,
        ],
    ];

    public function boot(): void
    {
        //
    }
}
