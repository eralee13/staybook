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
            $table->string('description_en')->nullable()->after('code');
            $table->integer('tourmind_id')->nullable()->after('description_en');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categoryRooms', function (Blueprint $table) {
            $table->dropColumn('description_en');
            $table->dropColumn('tourmind_id');
        });
    }
};
