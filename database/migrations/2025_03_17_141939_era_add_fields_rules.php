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
        Schema::table('rules', function (Blueprint $table) {
            $table->integer('amount')->after('date_book');
            //$table->string('currency', 3)->nullable()->after('amount');
            $table->dateTime('start_date_time')->after('currency');
            $table->dateTime('end_date_time')->after('currency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rules', function (Blueprint $table) {
            $table->dropColumn('amount');
            $table->dropColumn('start_date_time')->nullable()->after('currency');
            $table->dropColumn('end_date_time')->nullable()->after('currency');
        });
    }
};
