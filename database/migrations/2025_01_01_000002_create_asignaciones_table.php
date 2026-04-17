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
        // En create_asignaciones_table.php
        Schema::create('asignaciones', function (Blueprint $table) {
            $table->id('id_asignacion');
            // Asegúrate que 'personas' tenga 'id_persona' como llave primaria
            $table->foreignId('id_persona')->constrained('personas', 'id_persona');
            $table->foreignId('id_tipo_equipo')->constrained('tipos_equipo', 'id_tipo_equipo');
            $table->string('marca')->nullable();
            $table->string('modelo')->nullable();
            $table->string('serie')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asignaciones');
    }
};
