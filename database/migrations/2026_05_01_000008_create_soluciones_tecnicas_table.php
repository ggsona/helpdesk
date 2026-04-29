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
        Schema::create('soluciones_tecnicas', function (Blueprint $table) {
            $table->id('id_solucion');
            $table->foreignId('id_ticket')->constrained('tickets', 'id_ticket');
            $table->foreignId('id_usuario_tecnico')->constrained('users', 'id');
            
            // Resumen para el usuario (público)
            $table->string('resumen_usuario'); 
            
            // Procedimiento detallado con imágenes/pasos (interno/blog)
            $table->longText('procedimiento_detallado'); 
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soluciones_tecnicas');
    }
};
