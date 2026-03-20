<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\Product;
use App\Observers\OrderObserver;
use Illuminate\Support\ServiceProvider;

class ObserverServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Product::observe([]);
        Order::observe(OrderObserver::class);
    }
}
