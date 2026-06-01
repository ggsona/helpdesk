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
        Schema::create('articulos_conocimiento', function (Blueprint $table) {
            $table->id('id_articulo');
            
            // Origen del artículo
            $table->enum('origen', ['manual', 'ticket'])->default('manual');
            // Si viene de un ticket, referencia a la solución
            $table->unsignedBigInteger('id_solucion')->nullable();
            $table->foreign('id_solucion')->references('id_solucion')->on('soluciones_tecnicas')->nullOnDelete();
            
            // Contenido
            $table->string('titulo', 255);
            $table->string('slug', 300)->unique(); // URL amigable
            $table->text('extracto')->nullable(); // Preview/resumen corto (para cards)
            $table->longText('contenido'); // HTML enriquecido (Quill/TinyMCE)
            
            // Clasificación
            $table->unsignedBigInteger('id_categoria')->nullable();
            $table->foreign('id_categoria')->references('id_categoria')->on('categorias')->nullOnDelete();
            
            // Autoría
            $table->foreignId('id_autor')->constrained('users', 'id');
            $table->foreignId('id_editor')->nullable()->constrained('users', 'id'); // Último editor
            
            // Estado y visibilidad
            $table->enum('estado', ['borrador', 'publicado', 'archivado'])->default('borrador');
            $table->boolean('es_destacado')->default(false); // Artículo fijado/pinned
            $table->boolean('es_interno')->default(true); // true = solo staff, false = también usuarios
            
            // Métricas
            $table->unsignedInteger('vistas')->default(0);
            $table->unsignedInteger('veces_usado')->default(0); // Cuántas veces se usó para resolver un ticket
            
            $table->timestamp('fecha_publicacion')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articulos_conocimiento');
    }
};
