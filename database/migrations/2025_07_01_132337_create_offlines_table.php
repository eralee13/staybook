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
        Schema::create('offlines', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('arrivalDate');
            $table->string('departureDate');
            $table->string('city');
            $table->string('type')->nullable();
            $table->string('rating')->nullable();
            $table->string('room_count');
            $table->string('min_price')->nullable();
            $table->string('max_price')->nullable();
            $table->string('meal')->nullable();
            $table->string('accommodation')->nullable();
            $table->string('type_room')->nullable();
            $table->string('adult')->nullable();
            $table->string('child')->nullable();
            $table->string('childAges')->nullable();
            $table->string('message')->nullable();
            $table->string('file')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offlines');
    }
};
