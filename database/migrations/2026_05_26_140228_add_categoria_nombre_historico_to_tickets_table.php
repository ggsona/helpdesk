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
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('categoria_nombre_historico')->nullable()->after('id_categoria');
        });

        // Poblar retroactivamente con el nombre actual de las categorías
        $tickets = DB::table('tickets')->get();
        foreach ($tickets as $ticket) {
            $category = DB::table('categorias')
                ->where('id_categoria', $ticket->id_categoria)
                ->first();

            if ($category) {
                DB::table('tickets')
                    ->where('id_ticket', $ticket->id_ticket)
                    ->update(['categoria_nombre_historico' => $category->nombre_categoria]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('categoria_nombre_historico');
        });
    }
};
