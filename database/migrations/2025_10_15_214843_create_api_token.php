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
        Schema::create('api_tokens', function (Blueprint $t) {
            $t->id();
            $t->string('name');                       // удобное имя: "Partner A"
            $t->string('prefix', 8);                  // видимый префикс токена
            $t->string('token_hash', 64);             // sha256 хэш
            $t->json('scopes')->nullable();           // напр. ["catalog.read","availability.read"]
            $t->unsignedBigInteger('partner_id')->nullable(); // если есть сущность "партнёр"
            $t->timestamp('last_used_at')->nullable();
            $t->timestamp('expires_at')->nullable();
            $t->timestamps();

            $t->unique(['prefix','token_hash']);
            $t->index('partner_id');
            $t->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_tokens');
    }
};
