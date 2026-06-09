<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lotes', function (Blueprint $table) {
            $table->enum('cuota_inicial_tipo', ['monto', 'porcentaje'])->default('monto')->after('precio');
            $table->decimal('cuota_inicial_valor', 12, 2)->default(0)->after('cuota_inicial_tipo');
        });
    }

    public function down(): void
    {
        Schema::table('lotes', function (Blueprint $table) {
            $table->dropColumn(['cuota_inicial_tipo', 'cuota_inicial_valor']);
        });
    }
};
