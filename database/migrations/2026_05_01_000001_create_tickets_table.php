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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id('id_ticket');
            $table->string('asunto'); // <-- Agrégalo aquí
            $table->foreignId('id_usuario')->constrained('users', 'id');
            $table->foreignId('id_tipo_equipo')->constrained('tipos_equipo', 'id_tipo_equipo');
            $table->foreignId('id_prioridad')->constrained('prioridades', 'id_prioridad');
            $table->foreignId('id_categoria')->constrained('categorias', 'id_categoria');
            $table->text('descripcion_problema');
            $table->integer('estatus')->default(1);
            $table->foreignId('id_usuario_tecnico')->nullable()->constrained('users', 'id');
            $table->timestamp('fecha_cierre')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
