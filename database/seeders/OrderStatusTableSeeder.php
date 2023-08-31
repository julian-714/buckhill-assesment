<?php

namespace Database\Seeders;

use App\Models\OrderStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Str;

class OrderStatusTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $status = [
            [
                "uuid" => Str::orderedUuid(),
                "title" => "canceled",
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [
                "uuid" => Str::orderedUuid(),
                "title" => "shipped",
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [
                "uuid" => Str::orderedUuid(),
                "title" => "paid",
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [
                "uuid" => Str::orderedUuid(),
                "title" => "pending payment",
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [
                "uuid" => Str::orderedUuid(),
                "title" => "open",
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ]
        ];

        OrderStatus::insert($status);
    }
}
