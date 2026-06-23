<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('urbanizacion_referencias', function (Blueprint $table): void {
            $table->decimal('plano_x', 6, 3)->nullable()->after('longitud');
            $table->decimal('plano_y', 6, 3)->nullable()->after('plano_x');
        });
    }

    public function down(): void
    {
        Schema::table('urbanizacion_referencias', function (Blueprint $table): void {
            $table->dropColumn(['plano_x', 'plano_y']);
        });
    }
};
