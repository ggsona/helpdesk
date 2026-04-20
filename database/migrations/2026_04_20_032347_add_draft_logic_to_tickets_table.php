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
        Schema::table('tickets', function (Blueprint $table) {
        // Cambiamos 'id_estado' por 'estatus' que es el que ya usas
        $table->unsignedBigInteger('estatus')->default(1)->change(); 
        
        // Si 'id_tecnico' no existe en tu tabla original, cámbialo a 'create'
        // Aquí lo marcamos como nullable para que el borrador funcione
        $table->unsignedBigInteger('id_prioridad')->nullable()->change();
        
        if (!Schema::hasColumn('tickets', 'id_tecnico')) {
            $table->unsignedBigInteger('id_tecnico')->nullable()->after('id_usuario');
        } else {
            $table->unsignedBigInteger('id_tecnico')->nullable()->change();
        }
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            //
        });
    }
};
