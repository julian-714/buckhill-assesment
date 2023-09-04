<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); //FK user.id
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('order_status_id'); //FK order_statuses.id
            $table->foreign('order_status_id')->references('id')->on('order_statuses')->onDelete('cascade');
            $table->unsignedBigInteger('payment_id'); // FK payments.id
            $table->foreign('payment_id')->references('id')->on('payments')->onDelete('cascade');
            $table->uuid('uuid');
            $table->json('products'); //This field contains an array of products UUIDs Copy as text [ { "product" : "string_uuid" , "quantity" : int } ]
            $table->json('address'); //This field contains an object including the billing and shipping address Copy as text { "billing" : "string" , "shipping" : "string" }
            $table->float('delivery_fee', 8, 2)->default(0);
            $table->float('amount', 10, 2);
            $table->timestamps();
            $table->dateTime('shipped_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
