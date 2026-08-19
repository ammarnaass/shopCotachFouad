<?php

namespace App\Providers;

use App\Data\Repositories\Contracts\CouponRepositoryInterface;
use App\Data\Repositories\Contracts\OrderRepositoryInterface;
use App\Data\Repositories\Contracts\ProductRepositoryInterface;
use App\Data\Repositories\Contracts\SettingsRepositoryInterface;
use App\Data\Repositories\Eloquent\EloquentCouponRepository;
use App\Data\Repositories\Eloquent\EloquentOrderRepository;
use App\Data\Repositories\Eloquent\EloquentProductRepository;
use App\Data\Repositories\Eloquent\EloquentSettingsRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(OrderRepositoryInterface::class, EloquentOrderRepository::class);
        $this->app->bind(ProductRepositoryInterface::class, EloquentProductRepository::class);
        $this->app->bind(CouponRepositoryInterface::class, EloquentCouponRepository::class);
        $this->app->bind(SettingsRepositoryInterface::class, EloquentSettingsRepository::class);
    }

    public function boot(): void
    {
    }
}
