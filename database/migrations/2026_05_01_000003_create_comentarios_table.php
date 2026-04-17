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
        // En create_comentarios_table.php
        Schema::create('comentarios', function (Blueprint $table) {
            $table->id('id_comentario');
            $table->foreignId('id_ticket')->constrained('tickets', 'id_ticket')->onDelete('cascade');
            $table->foreignId('id_usuario')->constrained('users', 'id'); // <--- Cambio aquí
            $table->text('mensaje');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comentarios');
    }
};
