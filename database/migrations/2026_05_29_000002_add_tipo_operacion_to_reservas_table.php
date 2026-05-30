<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            if (! Schema::hasColumn('reservas', 'tipo_operacion')) {
                $table->string('tipo_operacion')->default('contado')->after('estado');
            }
        });

        DB::table('reservas')->whereNull('tipo_operacion')->update(['tipo_operacion' => 'contado']);

        Schema::table('reservas', function (Blueprint $table) {
            $table->index(['tipo_operacion', 'estado'], 'reservas_tipo_estado_index');
            $table->index(['usuario_id', 'fecha_reserva'], 'reservas_usuario_fecha_index');
        });
    }

    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->dropIndex('reservas_tipo_estado_index');
            $table->dropIndex('reservas_usuario_fecha_index');
            $table->dropColumn('tipo_operacion');
        });
    }
};
