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
            $table->integer('type_code')->nullable()->after('description_en');
            $table->integer('room_id')->nullable()->after('type_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categoryRooms', function (Blueprint $table) {
            $table->dropColumn('type_code');
        });
    }
};
