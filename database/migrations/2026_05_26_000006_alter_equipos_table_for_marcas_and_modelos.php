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
        Schema::table('equipos', function (Blueprint $table) {
            $table->dropColumn(['marca', 'modelo']);
            
            $table->unsignedBigInteger('id_marca')->nullable()->after('numero_bien');
            $table->foreign('id_marca')
                  ->references('id_marca')
                  ->on('marcas')
                  ->onDelete('set null');
                  
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
        Schema::table('equipos', function (Blueprint $table) {
            $table->dropForeign(['id_marca']);
            $table->dropForeign(['id_modelo']);
            
            $table->dropColumn(['id_marca', 'id_modelo']);
            
            $table->string('marca')->nullable()->after('nombre');
            $table->string('modelo')->nullable()->after('marca');
        });
    }
};
