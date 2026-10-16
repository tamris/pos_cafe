<?php

namespace App\Providers;

use App\Services\GeminiAIService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(GeminiAIService::class, function ($app) {
            return new GeminiAIService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        config(['app.locale' => 'id']);
        \Carbon\Carbon::setLocale('id');
        date_default_timezone_set('Asia/Jakarta');

        if (!$this->app->runningInConsole()) {
            $isLocalDev = str_contains(request()->getHost(), 'localhost') 
                || str_contains(request()->getHost(), '127.0.0.1');

            if (!$isLocalDev && ($this->app->environment('production') || request()->server('HTTP_X_FORWARDED_PROTO') === 'https' || request()->isSecure())) {
                \Illuminate\Support\Facades\URL::forceScheme('https');
            }
        } elseif ($this->app->environment('production') || str_starts_with((string) config('app.url'), 'https://')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    }
}
