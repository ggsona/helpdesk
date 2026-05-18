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
        Schema::table('personas', function (Blueprint $table) {
            $table->dropForeign(['id_oficina']);
            $table->renameColumn('id_oficina', 'id_unidad_administrativa');
        });

        Schema::table('personas', function (Blueprint $table) {
            $table->unsignedBigInteger('id_unidad_administrativa')->nullable()->change();
            $table->foreign('id_unidad_administrativa')
                  ->references('id')->on('unidades_administrativas')
                  ->nullOnDelete();
        });

        Schema::dropIfExists('oficinas');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('oficinas', function (Blueprint $table) {
            $table->id('id_oficina');
            $table->string('nombre_oficina');
            $table->timestamps();
        });

        Schema::table('personas', function (Blueprint $table) {
            $table->dropForeign(['id_unidad_administrativa']);
            $table->renameColumn('id_unidad_administrativa', 'id_oficina');
        });

        Schema::table('personas', function (Blueprint $table) {
            $table->foreign('id_oficina')->references('id_oficina')->on('oficinas');
        });
    }
};
