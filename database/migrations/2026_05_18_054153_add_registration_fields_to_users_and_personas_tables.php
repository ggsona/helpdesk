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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_approved')->default(false)->after('password');
        });

        Schema::table('personas', function (Blueprint $table) {
            $table->string('cedula')->unique()->nullable()->after('id_persona');
            $table->string('segundo_nombre')->nullable()->after('nombre');
            $table->string('segundo_apellido')->nullable()->after('apellido');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_approved');
        });

        Schema::table('personas', function (Blueprint $table) {
            $table->dropColumn(['cedula', 'segundo_nombre', 'segundo_apellido']);
        });
    }
};
