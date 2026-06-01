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
        Schema::create('articulo_adjuntos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_articulo');
            $table->foreign('id_articulo')->references('id_articulo')->on('articulos_conocimiento')->cascadeOnDelete();
            
            $table->string('nombre_original');      // "HWMonitor_v1.49.exe"
            $table->string('ruta_archivo');          // "articulos/adjuntos/2026/06/hwmonitor.exe"
            $table->string('tipo_mime', 100);       // "application/x-msdownload"
            $table->unsignedBigInteger('tamano');   // En bytes (máx 1 GB = 1073741824)
            $table->text('descripcion')->nullable(); // "Monitor de temperaturas del CPU/GPU"
            $table->unsignedInteger('descargas')->default(0); // Contador de descargas
            
            $table->foreignId('subido_por')->constrained('users', 'id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articulo_adjuntos');
    }
};
