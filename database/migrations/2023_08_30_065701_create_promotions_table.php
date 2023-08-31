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
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->string('title', 255);
            $table->text('content');
            $table->json('metadata'); //Example of the base content, can grow on demand. Copy as text { "valid_from" : "date(Y-m-d)" , "valid_to" : "date(Y-m-d)" , "image" : "UUID from petshop.files" }
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
