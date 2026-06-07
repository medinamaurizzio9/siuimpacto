<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table): void {
            $table->decimal('saldo_financiar', 12, 2)->default(0)->after('cuota_inicial');
        });

        DB::table('ventas')->orderBy('id')->each(function ($venta): void {
            $pagado = (float) DB::table('cuotas')->where('venta_id', $venta->id)->sum('monto_pagado');

            DB::table('ventas')->where('id', $venta->id)->update([
                'saldo_financiar' => (int) $venta->numero_cuotas === 0
                    ? 0
                    : max(0, (float) $venta->precio_final - (float) $venta->cuota_inicial - $pagado),
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table): void {
            $table->dropColumn('saldo_financiar');
        });
    }
};
