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
        Schema::create('movie_views', function (Blueprint $table) {
            $table->id();

            // fecha de inicio y fin de lectura
            $table->date('start_view');
            $table->date('end_view')->nullable();
            $table->text('notes')->nullable();

            // relaciones
            $table->foreignId('movie_id')->constrained()->onDelete('cascade');

            // union adicional
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movie_views');
    }
};
