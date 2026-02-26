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
        Schema::create('book_reads', function (Blueprint $table) {
            $table->id();

            // fecha de inicio y fin de lectura
            $table->date('start_read')->nullable();
            $table->date('end_read')->nullable();

            // relaciones
            $table->foreignId('book_id')->constrained()->onDelete('cascade');

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
        Schema::dropIfExists('book_reads');
    }
};
