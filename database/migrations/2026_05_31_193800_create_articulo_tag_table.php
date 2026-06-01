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
        Schema::create('articulo_tag', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_articulo');
            $table->foreignId('id_tag')->constrained('tags')->cascadeOnDelete();
            $table->foreign('id_articulo')->references('id_articulo')->on('articulos_conocimiento')->cascadeOnDelete();
            $table->unique(['id_articulo', 'id_tag']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articulo_tag');
    }
};
