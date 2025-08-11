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
        Schema::create('currency_rates', function (Blueprint $table) {
            $table->id();
            $table->decimal('usd', 10, 4)->nullable(); 
            $table->decimal('eur', 10, 4)->nullable(); 
            $table->decimal('rub', 10, 4)->nullable();
            $table->decimal('kzt', 10, 4)->nullable();
            $table->decimal('uzs', 10, 4)->nullable();
            $table->decimal('cny', 10, 4)->nullable();
            $table->boolean('status')->default(false); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currency_rates');
    }
};
