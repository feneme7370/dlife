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
        Schema::create('book_book_genre', function (Blueprint $table) {
            $table->id();

            // relacion intermedia con el libro
            $table->foreignId('book_id')->constrained()->onDelete('cascade');
            $table->foreignId('book_genre_id')->constrained()->onDelete('cascade');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_book_genre');
    }
};
