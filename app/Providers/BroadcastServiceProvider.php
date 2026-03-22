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
        // Customer (web guard) channel auth
        Broadcast::routes(['middleware' => ['web']]);

        // Admin panel channel auth
        Broadcast::routes(['middleware' => ['web', 'admin'], 'prefix' => 'admin']);

        // Vendor panel channel auth
        Broadcast::routes(['middleware' => ['web', 'seller'], 'prefix' => 'vendor']);

        require base_path('routes/channels.php');
    }
}
