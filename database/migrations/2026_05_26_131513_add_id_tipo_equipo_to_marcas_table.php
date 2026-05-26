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
        Schema::table('marcas', function (Blueprint $table) {
            $table->unsignedBigInteger('id_tipo_equipo')->nullable()->after('id_marca');
            $table->foreign('id_tipo_equipo')
                  ->references('id_tipo_equipo')
                  ->on('tipos_equipo')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marcas', function (Blueprint $table) {
            $table->dropForeign(['id_tipo_equipo']);
            $table->dropColumn('id_tipo_equipo');
        });
    }
};
