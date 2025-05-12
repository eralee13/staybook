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
        Schema::create('cancellation_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Название правила ("Гибкая отмена", "Без возврата" и т.д.)
            $table->boolean('is_refundable')->default(true); // Можно ли отменить
            $table->integer('free_cancellation_days')->nullable(); // За сколько дней можно отменить бесплатно
            $table->enum('penalty_type', ['fixed', 'percent'])->nullable(); // Тип штрафа: фикс или процент
            $table->decimal('penalty_amount', 8, 2)->nullable(); // Сумма штрафа или процент
            $table->text('description')->nullable(); // Текстовое описание условий
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rates', function (Blueprint $table) {
            $table->dropColumn(['desc_en', 'rate_code', 'allotment', 'currency', 'total_price', 'refundable']);
            $table->integer('availability');
            $table->integer('adult')->default(1);
            $table->integer('child')->nullable();
            $table->boolean('children_allowed');
            $table->integer('free_children_age');
            $table->decimal('child_extra_fee');
            $table->text('children_policy');
            $table->string('bed_type');
            $table->softDeletes();
        });
    }
};
