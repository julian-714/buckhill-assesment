<?php

namespace Logicrays\StripePayment;

use Illuminate\Support\ServiceProvider;

class StripePaymentServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/stripe-payment.php' => config_path('stripe-payment.php'),
        ], 'config');
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
    }
}
