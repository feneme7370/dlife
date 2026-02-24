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
        Schema::create('collections', function (Blueprint $table) {
            $table->id();

            // nombre
            $table->string('name');
            $table->string('slug');
            
            // descripciones del item
            $table->text('description')->nullable();
            $table->integer('books_amount')->default(0);
            $table->integer('movies_amount')->default(0);

            // url de la imagen
            $table->text('cover_image')->nullable();
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
        Schema::dropIfExists('collections');
    }
};
