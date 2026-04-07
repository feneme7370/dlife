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
        Schema::create('game_playeds', function (Blueprint $table) {
            $table->id();

            // fecha de inicio y fin de lectura
            $table->date('start_played');
            $table->date('end_played')->nullable();
            $table->text('notes')->nullable();

            // relaciones
            $table->foreignId('game_id')->constrained()->onDelete('cascade');

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
        Schema::dropIfExists('game_playeds');
    }
};
