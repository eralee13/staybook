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
        Schema::table('hotels', function (Blueprint $table) {
            $table->json('metapolicy_struct')->nullable(); // JSON-поле для хранения массива
            $table->string('metapolicy_extra_info', 255)->nullable(); // Дополнительная информация
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->dropColumn('metapolicy_struct');
            $table->dropColumn('metapolicy_extra_info');
        });
    }
};
