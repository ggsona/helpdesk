<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::create('ticket_adjuntos', function (Blueprint $table) {
        $table->id();
        $table->foreignId('id_ticket')->constrained('tickets', 'id_ticket')->onDelete('cascade');
        $table->string('ruta_archivo'); 
        $table->string('nombre_original');
        $table->string('tipo_mimo'); // Ej: image/jpeg, video/mp4, application/pdf
        $table->bigInteger('tamano'); // Tamaño en bytes
        $table->timestamps();
    });
}
};
