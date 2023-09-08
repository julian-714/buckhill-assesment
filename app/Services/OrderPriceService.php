<?php

namespace App\Services;

use Logicrays\StripePayment\Controllers\StripePaymentController;
use Illuminate\Support\Str;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class OrderPriceService
{
    public function calculatePrice($price, $quantity, $deliveryFee = 15)
    {
        $subTotal = $price * $quantity;
        if ($subTotal < 500) {
            return $total = $subTotal + $deliveryFee;
        }
        return $subTotal;
    }

    public function createStripeCheckout()
    {
        $uuid = '9a11207f-33b8-49b1-80ac-b548f1a8a53c';
        $paymentProcessor = new StripePaymentController();
        return $paymentProcessor->processPayment($uuid);
    }
}
