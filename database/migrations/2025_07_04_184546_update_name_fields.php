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
            $table->string('title3')->nullable();
            $table->string('title4')->nullable();
            $table->string('title5')->nullable();
            $table->string('title6')->nullable();
            $table->string('title7')->nullable();
            $table->string('title8')->nullable();
            $table->string('child_name')->nullable();
            $table->string('child_name2')->nullable();
            $table->string('child_name3')->nullable();
            $table->string('child_name4')->nullable();
            $table->string('child_name5')->nullable();
            $table->string('child_name6')->nullable();
            $table->string('child_name7')->nullable();
            $table->string('child_name8')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
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
};
