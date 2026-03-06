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
        Schema::create('series', function (Blueprint $table) {
            $table->id();

            // titulos
            $table->string('title');
            $table->string('slug')->nullable();
            $table->string('original_title')->nullable();

            // datos de serie y pelicula
            $table->text('synopsis')->nullable();
            $table->integer('start_date')->nullable();
	        $table->integer('end_date')->nullable();

            // datos adicionales
            $table->decimal('number_collection', 4, 2)->nullable();

            // series
            $table->integer('seasons')->nullable();
            $table->integer('episodes')->nullable();

            // resumen del libro
            $table->longText('summary')->nullable();
            $table->longText('summary_clear')->nullable();

            // notas del libro
            $table->longText('notes')->nullable();
            $table->longText('notes_clear')->nullable();

            // opinion del libro
            $table->boolean('is_favorite')->default(0); // 0 false - 1 true
            $table->boolean('is_abandonated')->default(0); // 0 false - 1 true
            $table->boolean('is_public')->default(0); // 0 false - 1 true

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
        Schema::dropIfExists('series');
    }
};
