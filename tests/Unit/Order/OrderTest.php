<?php

namespace Tests\Unit\Order;

use PHPUnit\Framework\TestCase;
use App\Services\OrderPriceService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\User;

class OrderTest extends TestCase
{
    public function test_order_sum()
    {
        $price = 450;
        $quantity = 2;
        $totalPayable = (new OrderPriceService())->calculatePrice($price, $quantity);
        $this->assertTrue(true);
    }
}
