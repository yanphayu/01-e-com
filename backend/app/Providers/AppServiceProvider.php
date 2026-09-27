<?php

namespace App\Providers;

use App\Notifications\ProductReported;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('admin.layouts.app', function ($view) {
            $user = auth()->user();

            $view->with('appUnreadReportCount', $user
                ? $user->unreadNotifications()->where('type', ProductReported::class)->count()
                : 0);
        });

        $this->configureOtpRateLimits();
    }

    /**
     * Limit how often a verification code may be mailed out or submitted back.
     */
    protected function configureOtpRateLimits(): void
    {
        RateLimiter::for('otp-send', function (Request $request) {
            $email = $request->input('email') ?? $request->user()?->email;

            return [
                Limit::perMinute((int) config('otp.throttle.send', 3))
                    ->by('otp-send:'.Str::lower((string) $email)),
                Limit::perMinute((int) config('otp.throttle.send_per_ip', 10))
                    ->by('otp-send-ip:'.$request->ip()),
            ];
        });

        RateLimiter::for('otp-verify', fn (Request $request) => Limit::perMinute((int) config('otp.throttle.verify', 10))
            ->by('otp-verify:'.$request->ip()));
    }
}
