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
        Schema::create('unidades_administrativas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre'); // Ej: Tecnología, Ventas, Piso 1
            $table->foreignId('id_nivel')->constrained('niveles_jerarquicos')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('unidades_administrativas')->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unidades_administrativas');
    }
};
