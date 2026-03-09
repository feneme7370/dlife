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
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();

            // titulos
            $table->string('title');
            $table->string('slug')->unique();

            // datos de serie y pelicula
            $table->text('description');

            // resumen del libro
            $table->longText('ingredients')->nullable();
            $table->longText('ingredients_clear')->nullable();

            // notas del libro
            $table->longText('instructions')->nullable();
            $table->longText('instructions_clear')->nullable();

            // opinion del libro
            $table->boolean('is_public')->default(0); // 0 false - 1 true

            // imagenes del libro
            $table->string('cover_image')->nullable();
            $table->text('cover_image_url')->nullable();
            
            // datos internos            
            $table->string('uuid')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipes');
    }
};
