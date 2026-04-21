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
        Schema::create('ticket_comentarios', function (Blueprint $table) {
            $table->id('id_comentario');
            // Relación con el ticket (asegúrate que el nombre de la tabla sea 'tickets' y el PK 'id_ticket')
            $table->foreignId('id_ticket')->constrained('tickets', 'id_ticket')->onDelete('cascade');
            // Quién escribe el mensaje
            $table->foreignId('id_usuario')->constrained('users');
            
            $table->text('mensaje');
            $table->boolean('es_interno')->default(false); // true = solo técnicos, false = público
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_comentarios');
    }
};
