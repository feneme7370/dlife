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
        Schema::create('blogs', function (Blueprint $table) {
            $table->id();

            // contenido principal
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable(); // resumen corto
            $table->longText('content')->nullable();
            $table->longText('content_clear')->nullable();

            // tipo de contenido
            $table->string('type')->nullable(); // ejemplo: tip, charla, idea, pensamiento, resumen

            // visibilidad
            $table->boolean('is_public')->default(false);

            // portada
            $table->string('cover_image')->nullable();
            $table->text('cover_image_url')->nullable();

            // datos internos
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blogs');
    }
};
