<?php

namespace Logicrays\OrderStatusNotifier;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Logicrays\OrderStatusNotifier\Listeners\SendTeamsNotification;

class OrderStatusNotifierServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/orderstatusnotifier.php' => config_path('orderstatusnotifier.php'),
        ], 'config');

        // Register the event and listener
        Event::listen(OrderStatusNotifier::class, SendTeamsNotification::class);
    }
}
