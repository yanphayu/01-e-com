<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Policies\CategoryPolicy;
use App\Policies\OrderPolicy;
use App\Policies\ProductPolicy;
use App\Policies\ReviewPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Sanctum\Sanctum;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        User::class => UserPolicy::class,
        Product::class => ProductPolicy::class,
        Order::class => OrderPolicy::class,
        Review::class => ReviewPolicy::class,
        Category::class => CategoryPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        Sanctum::usePersonalAccessTokenModel(\App\Models\PersonalAccessToken::class);
    }
}
