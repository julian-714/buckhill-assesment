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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->uuid('category_uuid'); // uuid from categories table
            $table->string('title', 255);
            $table->float('price', 8, 2);
            $table->text('description');
            $table->json('metadata'); //Example of the base content, can grow on demand. Copy as text { "brand" : "UUID from petshop.brands" , "image" : "UUID from petshop.files" }
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
