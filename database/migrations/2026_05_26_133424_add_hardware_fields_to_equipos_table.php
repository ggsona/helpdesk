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
        // TiDB fix: each column that uses ->after() referencing a column
        // added in the same statement must be in a separate ALTER TABLE.
        Schema::table('equipos', function (Blueprint $table) {
            $table->string('ram')->nullable()->after('mac_address');
        });

        Schema::table('equipos', function (Blueprint $table) {
            $table->string('procesador')->nullable()->after('ram');
        });

        Schema::table('equipos', function (Blueprint $table) {
            $table->string('disco_duro')->nullable()->after('procesador');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipos', function (Blueprint $table) {
            $table->dropColumn(['ram', 'procesador', 'disco_duro']);
        });
    }
};
