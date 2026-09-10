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
        // TiDB fix: each ->after() that references the previous newly-added column
        // must be in a separate ALTER TABLE statement.
        Schema::table('soluciones_tecnicas', function (Blueprint $table) {
            $table->text('diagnostico')->nullable()->after('procedimiento_detallado');
        });

        Schema::table('soluciones_tecnicas', function (Blueprint $table) {
            $table->text('causa_raiz')->nullable()->after('diagnostico');
        });

        Schema::table('soluciones_tecnicas', function (Blueprint $table) {
            $table->text('acciones_preventivas')->nullable()->after('causa_raiz');
        });

        Schema::table('soluciones_tecnicas', function (Blueprint $table) {
            $table->string('tiempo_resolucion', 50)->nullable()->after('acciones_preventivas');
        });

        Schema::table('soluciones_tecnicas', function (Blueprint $table) {
            $table->enum('dificultad', ['basica', 'intermedia', 'avanzada'])->default('intermedia')->after('tiempo_resolucion');
        });

        Schema::table('soluciones_tecnicas', function (Blueprint $table) {
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
