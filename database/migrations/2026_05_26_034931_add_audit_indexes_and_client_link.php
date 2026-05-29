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
            $table->foreignId('cliente_id')->nullable()->after('id')->constrained('clientes')->nullOnDelete();
        });

        Schema::table('ventas', function (Blueprint $table) {
            $table->index(['cliente_id', 'fecha_venta']);
            $table->index(['lote_id', 'estado']);
            $table->index('user_id');
        });

        Schema::table('cuotas', function (Blueprint $table) {
            $table->index(['estado', 'fecha_programada']);
            $table->index(['venta_id', 'estado']);
        });

        Schema::table('cash_movements', function (Blueprint $table) {
            $table->index(['fecha', 'estado']);
            $table->index(['cliente_id', 'estado']);
            $table->index(['sale_id', 'concepto']);
            $table->index(['reservation_id', 'concepto']);
            $table->index(['installment_id', 'concepto']);
        });

        Schema::table('reservas', function (Blueprint $table) {
            $table->index(['estado', 'fecha_vencimiento']);
            $table->index(['cliente_id', 'estado']);
            $table->index(['lote_id', 'estado']);
        });

        Schema::table('lotes', function (Blueprint $table) {
            $table->index(['estado', 'manzano_id']);
            $table->index(['coord_x', 'coord_y']);
        });

        Schema::table('lot_histories', function (Blueprint $table) {
            $table->index(['lote_id', 'created_at']);
            $table->index(['user_id', 'accion']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lot_histories', function (Blueprint $table) {
            $table->dropIndex(['lote_id', 'created_at']);
            $table->dropIndex(['user_id', 'accion']);
        });

        Schema::table('lotes', function (Blueprint $table) {
            $table->dropIndex(['estado', 'manzano_id']);
            $table->dropIndex(['coord_x', 'coord_y']);
        });

        Schema::table('reservas', function (Blueprint $table) {
            $table->dropIndex(['estado', 'fecha_vencimiento']);
            $table->dropIndex(['cliente_id', 'estado']);
            $table->dropIndex(['lote_id', 'estado']);
        });

        Schema::table('cash_movements', function (Blueprint $table) {
            $table->dropIndex(['fecha', 'estado']);
            $table->dropIndex(['cliente_id', 'estado']);
            $table->dropIndex(['sale_id', 'concepto']);
            $table->dropIndex(['reservation_id', 'concepto']);
            $table->dropIndex(['installment_id', 'concepto']);
        });

        Schema::table('cuotas', function (Blueprint $table) {
            $table->dropIndex(['estado', 'fecha_programada']);
            $table->dropIndex(['venta_id', 'estado']);
        });

        Schema::table('ventas', function (Blueprint $table) {
            $table->dropIndex(['cliente_id', 'fecha_venta']);
            $table->dropIndex(['lote_id', 'estado']);
            $table->dropIndex(['user_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cliente_id');
        });
    }
};
