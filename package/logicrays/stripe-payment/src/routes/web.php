<?php
// routes/web.php in your package

use Illuminate\Support\Facades\Route;
use Logicrays\StripePayment\Controllers\StripePaymentController;

Route::get('stripe/payment/{order_uuid}', [StripePaymentController::class, 'handleCallback'])->name('stripe.payment');
