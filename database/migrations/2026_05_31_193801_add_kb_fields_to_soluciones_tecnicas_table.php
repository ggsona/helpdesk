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
        Schema::table('soluciones_tecnicas', function (Blueprint $table) {
            $table->text('diagnostico')->nullable()->after('procedimiento_detallado');
            $table->text('causa_raiz')->nullable()->after('diagnostico');
            $table->text('acciones_preventivas')->nullable()->after('causa_raiz');
            $table->string('tiempo_resolucion', 50)->nullable()->after('acciones_preventivas');
            $table->enum('dificultad', ['basica', 'intermedia', 'avanzada'])->default('intermedia')->after('tiempo_resolucion');
            $table->boolean('publicar_en_kb')->default(false)->after('dificultad');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('soluciones_tecnicas', function (Blueprint $table) {
            $table->dropColumn([
                'diagnostico',
                'causa_raiz',
                'acciones_preventivas',
                'tiempo_resolucion',
                'dificultad',
                'publicar_en_kb'
            ]);
        });
    }
};
