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
            $table->renameColumn('check_in_request', 'checkin_request');
            $table->renameColumn('check_out_request', 'checkout_request');
            $table->renameColumn('early_in', 'checkin_time');
            $table->renameColumn('late_out', 'checkout_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->renameColumn('checkin_request', 'check_in_request');
            $table->renameColumn('checkout_request', 'check_out_request');
            $table->renameColumn('checkin_time', 'early_in');
            $table->renameColumn('checkout_time', 'late_out');
        });
    }
};
