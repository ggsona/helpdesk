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
        Schema::create('ticket_asignaciones', function (Blueprint $table) {
            $table->id(); // ID propio de esta tabla
            
            // Relación con Tickets (usando tu columna id_ticket)
            $table->unsignedBigInteger('id_ticket');
            $table->foreign('id_ticket')
                ->references('id_ticket')
                ->on('tickets')
                ->onDelete('cascade');

            // Relación con Usuarios (Técnico)
            $table->unsignedBigInteger('id_usuario_tecnico');
            $table->foreign('id_usuario_tecnico')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            
            $table->timestamp('fecha_asignacion')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_asignaciones');
    }
};
