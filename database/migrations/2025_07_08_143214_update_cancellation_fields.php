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
        Schema::table('cancellation_rules', function (Blueprint $table) {
            $table->float('penalty_amount')->nullable()->change();
            $table->integer('penalty_nights')->nullable()->change();
            $table->unsignedBigInteger('hotel_id')->nullable()->change();
            $table->unsignedBigInteger('rate_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cancellation_rules', function (Blueprint $table) {
            //
        });
    }
};
