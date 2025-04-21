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
        Schema::table('users', function (Blueprint $table) {
            $table->string('bank_name')->nullable();
            $table->string('bank_inn')->nullable();
            $table->string('bank_account')->nullable();
            $table->string('bank_bic')->nullable();
            $table->string('address')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('bank_name');
            $table->dropColumn('bank_inn');
            $table->dropColumn('bank_account');
            $table->dropColumn('bank_bic');
            $table->dropColumn('address');
        });
    }
};
