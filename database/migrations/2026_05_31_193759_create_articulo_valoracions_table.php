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
        Schema::create('articulo_valoraciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_articulo');
            $table->foreign('id_articulo')->references('id_articulo')->on('articulos_conocimiento')->cascadeOnDelete();
            $table->foreignId('id_usuario')->constrained('users', 'id');
            
            $table->boolean('es_util'); // true = 👍, false = 👎
            $table->text('comentario')->nullable();
            
            $table->unique(['id_articulo', 'id_usuario']); // Un voto por usuario por artículo
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articulo_valoraciones');
    }
};
