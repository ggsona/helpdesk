<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Encontrar nombres duplicados
        $duplicates = DB::table('categorias')
            ->select('nombre_categoria')
            ->groupBy('nombre_categoria')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $dup) {
            // Obtener todos los IDs con este nombre, ordenados de menor a mayor
            $ids = DB::table('categorias')
                ->where('nombre_categoria', $dup->nombre_categoria)
                ->orderBy('id_categoria', 'asc')
                ->pluck('id_categoria');

            $keepId = $ids->first();
            $deleteIds = $ids->slice(1);

            // Reasignar tickets que apunten a los duplicados que eliminaremos
            DB::table('tickets')
                ->whereIn('id_categoria', $deleteIds)
                ->update(['id_categoria' => $keepId]);

            // Eliminar los duplicados
            DB::table('categorias')
                ->whereIn('id_categoria', $deleteIds)
                ->delete();
        }

        // 2. Agregar índice UNIQUE a nombre_categoria
        Schema::table('categorias', function (Blueprint $table) {
            $table->string('nombre_categoria', 100)->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categorias', function (Blueprint $table) {
            $table->dropUnique(['nombre_categoria']);
        });
    }
};
