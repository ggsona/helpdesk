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
        // TiDB fix: separate ALTER TABLE statements are required because
        // referencing a newly-added column (created_by) via AFTER in the
        // same ALTER TABLE statement is not supported in TiDB.
        Schema::table("categorias", function (Blueprint $table) {
            $table->unsignedBigInteger("created_by")->nullable()->after("updated_at");
            $table->foreign("created_by")->references("id")->on("users")->onDelete("set null");
        });

        Schema::table("categorias", function (Blueprint $table) {
            $table->unsignedBigInteger("updated_by")->nullable()->after("created_by");
            $table->foreign("updated_by")->references("id")->on("users")->onDelete("set null");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("categorias", function (Blueprint $table) {
            $table->dropForeign(["created_by"]);
            $table->dropColumn("created_by");
            
            $table->dropForeign(["updated_by"]);
            $table->dropColumn("updated_by");
        });
    }
};
