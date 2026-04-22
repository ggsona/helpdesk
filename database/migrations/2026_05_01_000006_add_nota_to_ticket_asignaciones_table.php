<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('ticket_asignaciones', function (Blueprint $table) {
            // Agregamos el campo para guardar sugerencias o motivos
            $table->text('nota')->nullable()->after('id_usuario_tecnico');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ticket_asignaciones', function (Blueprint $table) {
            //
        });
    }
};
