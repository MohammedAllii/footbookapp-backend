<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    Schema::create('prenotaziones', function (Blueprint $table) {
        $table->id();
        $table->foreignId('campi_id')->constrained('campis')->onDelete('cascade');
        $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
        $table->date('data');
        $table->string('ora'); 
        $table->double('prezzo');
        $table->string('payment_status')->default('pending');
        $table->string('payment_intent_id')->nullable();
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prenotaziones');
    }
};
