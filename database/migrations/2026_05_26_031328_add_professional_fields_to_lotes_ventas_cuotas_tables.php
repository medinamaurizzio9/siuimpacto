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
        Schema::table('lotes', function (Blueprint $table) {
            $table->decimal('coord_x', 6, 2)->nullable()->after('columna');
            $table->decimal('coord_y', 6, 2)->nullable()->after('coord_x');
        });

        Schema::table('ventas', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('cliente_id')->constrained('users')->nullOnDelete();
            $table->foreignId('reserva_id')->nullable()->after('user_id')->constrained('reservas')->nullOnDelete();
        });

        Schema::table('cuotas', function (Blueprint $table) {
            $table->date('fecha_programada')->nullable()->after('numero');
            $table->decimal('saldo_pendiente', 12, 2)->default(0)->after('monto_pagado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cuotas', function (Blueprint $table) {
            $table->dropColumn(['fecha_programada', 'saldo_pendiente']);
        });

        Schema::table('ventas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reserva_id');
            $table->dropConstrainedForeignId('user_id');
        });

        Schema::table('lotes', function (Blueprint $table) {
            $table->dropColumn(['coord_x', 'coord_y']);
        });
    }
};
