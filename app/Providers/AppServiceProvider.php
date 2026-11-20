<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->environment('local')) {
            if (class_exists(\Laravel\Boost\BoostServiceProvider::class)) {
                $this->app->register(\Laravel\Boost\BoostServiceProvider::class);
            }
            if (class_exists(\NunoMaduro\Collision\Adapters\Laravel\CollisionServiceProvider::class)) {
                $this->app->register(\NunoMaduro\Collision\Adapters\Laravel\CollisionServiceProvider::class);
            }
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // تجاوز مدير النظام لجميع الفحوصات بصلاحية كاملة
        Gate::before(function ($user, $ability) {
            return $user->isAdmin() ? true : null;
        });

        // وسم Blade مخصص لفحص الصلاحيات بسهولة في الواجهات
        Blade::if('hasPermission', function (string $permission) {
            return auth()->check() && auth()->user()->hasPermission($permission);
        });

        if ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') || (!empty($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'production') || isset($_SERVER['VERCEL']) || getenv('VERCEL')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    }
}
