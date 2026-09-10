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
        // TiDB fix: each Schema::table() generates a separate ALTER TABLE.
        // Cannot mix DROP + ADD columns, nor reference a newly-added column
        // via AFTER in the same statement.

        // 1) Drop old text columns first
        Schema::table('equipos', function (Blueprint $table) {
            $table->dropColumn(['marca', 'modelo']);
        });

        // 2) Add id_marca with its FK
        Schema::table('equipos', function (Blueprint $table) {
            $table->unsignedBigInteger('id_marca')->nullable()->after('numero_bien');
            $table->foreign('id_marca')
                  ->references('id_marca')
                  ->on('marcas')
                  ->onDelete('set null');
        });

        // 3) Add id_modelo with its FK (after id_marca now exists)
        Schema::table('equipos', function (Blueprint $table) {
            $table->unsignedBigInteger('id_modelo')->nullable()->after('id_marca');
            $table->foreign('id_modelo')
                  ->references('id_modelo')
                  ->on('modelos')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // TiDB fix: separate statements for drop FK, drop columns, add columns
        Schema::table('equipos', function (Blueprint $table) {
            $table->dropForeign(['id_marca']);
            $table->dropForeign(['id_modelo']);
        });

        Schema::table('equipos', function (Blueprint $table) {
            $table->dropColumn(['id_marca', 'id_modelo']);
        });

        Schema::table('equipos', function (Blueprint $table) {
            $table->string('marca')->nullable()->after('nombre');
        });

        Schema::table('equipos', function (Blueprint $table) {
            $table->string('modelo')->nullable()->after('marca');
        });
    }
};
