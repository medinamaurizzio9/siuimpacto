<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('lotes', function (Blueprint $table) {
            $table->decimal('coord_x', 6, 2)->nullable()->change();
            $table->decimal('coord_y', 6, 2)->nullable()->change();
        });

        DB::table('lotes')->where('coord_x', 50)->where('coord_y', 50)->update([
            'coord_x' => null,
            'coord_y' => null,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('lotes')->whereNull('coord_x')->orWhereNull('coord_y')->update([
            'coord_x' => 50,
            'coord_y' => 50,
        ]);

        Schema::table('lotes', function (Blueprint $table) {
            $table->decimal('coord_x', 6, 2)->default(50)->nullable(false)->change();
            $table->decimal('coord_y', 6, 2)->default(50)->nullable(false)->change();
        });
    }
};
