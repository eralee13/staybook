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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->string('title');
            $table->string('title_en')->nullable();
            $table->string('description')->nullable();
            $table->string('description_en')->nullable();
            $table->integer('exely_id')->nullable();
            $table->integer('tourmind_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn(['id', 'title', 'title_en', 'description', 'description_en', 'exely_id', 'tourmind_id']);
        });
    }
};
