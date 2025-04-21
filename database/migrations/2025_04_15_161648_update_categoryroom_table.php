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
        Schema::table('categoryRooms', function (Blueprint $table) {
            $table->string('type_code')->nullable();
            $table->string('tourmind_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categoryRooms', function (Blueprint $table) {
            $table->dropColumn('type_code');
            $table->dropColumn('tourmind_id');
        });
    }
};
