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
        Schema::create('equipos', function (Blueprint $table) {
            $table->id('id_equipo');
            $table->string('nombre');
            $table->string('marca')->nullable();
            $table->string('modelo')->nullable();
            $table->string('numero_bien')->nullable()->unique();
            $table->string('ip_address')->nullable();
            $table->string('mac_address')->nullable();
            
            // Relación con tipo de equipo
            $table->unsignedBigInteger('id_tipo_equipo');
            $table->foreign('id_tipo_equipo')
                  ->references('id_tipo_equipo')
                  ->on('tipos_equipo')
                  ->onDelete('restrict');
            
            // Relación con usuario asignado
            $table->unsignedBigInteger('id_usuario_asignado')->nullable();
            $table->foreign('id_usuario_asignado')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
                  
            $table->boolean('estado')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipos');
    }
};
