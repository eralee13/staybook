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
        Schema::table('rates', function (Blueprint $table) {
            $table->string('rate_code', 255)->after('meal_id');
            $table->integer('allotment')->after('rate_code');
            $table->string('currency', 3)->after('allotment');
            $table->integer('total_price')->after('currency');
            $table->boolean('refundable')->default(false)->after('total_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rates', function (Blueprint $table) {
            $table->dropColumn('rate_code');
            $table->dropColumn('allotment');
            $table->dropColumn('currency');
            $table->dropColumn('total_price');
            $table->dropColumn('refundable');
        });
    }
};
