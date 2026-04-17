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
        Schema::create('prioridades', function (Blueprint $table) {
            // Asegúrate de que diga id_prioridad y NO solo id
            $table->id('id_prioridad'); 
            $table->string('nombre_prioridad');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prioridades');
    }
};
