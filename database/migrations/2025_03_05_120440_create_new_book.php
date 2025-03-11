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
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('title2')->nullable();
            $table->integer('hotel_id');
            $table->integer('room_id');
            $table->string('phone');
            $table->string('email');
            $table->string('comment')->nullable();
            $table->string('adult');
            $table->string('child')->nullable();
            $table->float('sum');
            $table->date('arrivalDate');
            $table->date('departureDate');
            $table->string('status')->nullable();
            $table->string('tag')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('new_book');
    }
};
