<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('hotels', function (Blueprint $table) {
            // добавь только существующие поля
            $table->fullText(['title', 'title_en', 'city']);
        });
    }

    public function down()
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->dropFullText(['title', 'title_en', 'city']);
        });
    }
};