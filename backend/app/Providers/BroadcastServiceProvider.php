<?php

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // The chat app authenticates private channels with a Sanctum Bearer
        // token (localStorage). The framework default mounts the broadcast
        // auth route with the session 'web' guard, which cannot read that
        // token -> Auth::user() is null -> 403 on every subscription.
        // Register the route under the sanctum guard so the Bearer resolves.
        // (route owner is now bootstrap/app.php ->withBroadcasting(..., auth:sanctum))
    }
}
