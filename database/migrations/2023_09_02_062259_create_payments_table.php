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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->string('type', 25); //Supported payments types Copy as text [ 'credit_card' , 'cash_on_delivery' , 'bank_transfer' , ] ;
            $table->json('details'); //Evey payment type is different, you must review the wireframe for more details Copy as text // Type: credit_card { "holder_name" : "string" , "number" : "string" , "ccv" : int , "expire_date" : "string" } , // Type: cash_on_delivery { "first_name" : "string" , "last_name" : "string" , "address" : "string" } , // Type: bank_transfer { "swift" : "string" , "iban" : "string" , "name" : "string" }
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
