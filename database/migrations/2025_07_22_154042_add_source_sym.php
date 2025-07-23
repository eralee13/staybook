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
            $table->string('source_sym')->nullable();
            $table->dropColumn('title2');
            $table->dropColumn('title3');
            $table->dropColumn('title4');
            $table->dropColumn('title5');
            $table->dropColumn('title6');
            $table->dropColumn('title7');
            $table->dropColumn('title8');
            $table->dropColumn('child_name');
            $table->dropColumn('child_name2');
            $table->dropColumn('child_name3');
            $table->dropColumn('child_name4');
            $table->dropColumn('child_name5');
            $table->dropColumn('child_name6');
            $table->dropColumn('child_name7');
            $table->dropColumn('child_name8');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn('source_sym');
        });
    }
};
