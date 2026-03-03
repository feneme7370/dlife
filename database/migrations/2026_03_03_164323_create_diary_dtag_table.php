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
        Schema::create('diary_dtag', function (Blueprint $table) {
            $table->id();

            // relacion intermedia con el libro
            $table->foreignId('diary_id')->constrained()->onDelete('cascade');
            $table->foreignId('dtag_id')->constrained()->onDelete('cascade');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diary_dtag');
    }
};
