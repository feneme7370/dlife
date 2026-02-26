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
        Schema::create('books', function (Blueprint $table) {
            $table->id();

            // titulos
            $table->string('title');
            $table->string('slug');
            $table->string('original_title')->nullable();

            // datos del libro
            $table->text('synopsis')->nullable();
            $table->integer('release_date')->nullable();

            // datos adicionales
            $table->integer('number_collection')->nullable();
            $table->integer('pages')->nullable();

            // resumen del libro
            $table->longText('summary')->nullable();
            $table->longText('summary_clear')->nullable();

            // notas del libro
            $table->longText('notes')->nullable();
            $table->longText('notes_clear')->nullable();

            // opinion del libro
            $table->boolean('is_favorite')->default(0); // 0 false - 1 true
            $table->boolean('is_abandonated')->default(0); // 0 false - 1 true

            // seleccionables desde el modelo
            $table->integer('rating')->nullable(); // sin valoracion, y de 1 a 5 estrellas

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
        Schema::dropIfExists('books');
    }
};
