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
        Schema::table('books', function (Blueprint $table) {
            $table->boolean('check_in_request')->default(false);
            $table->boolean('check_out_request')->default(false);
            $table->time('early_in')->nullable();
            $table->time('late_out')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn('check_in_request');
            $table->dropColumn('check_out_request');
            $table->dropColumn('early_in');
            $table->dropColumn('late_out');
        });
    }
};
