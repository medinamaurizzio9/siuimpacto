<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('urbanizacion_commercial_settings', function (Blueprint $table): void {
            $table->decimal('inicial_minima_usd', 12, 2)->default(0)->after('incremento_credito_valor');
            $table->boolean('plazo_12_habilitado')->default(true)->after('inicial_minima_usd');
            $table->boolean('plazo_24_habilitado')->default(true)->after('plazo_12_habilitado');
            $table->boolean('plazo_36_habilitado')->default(true)->after('plazo_24_habilitado');
            $table->boolean('descuento_contado_activo')->default(false)->after('plazo_36_habilitado');
            $table->string('descuento_contado_tipo')->default('monto')->after('descuento_contado_activo');
            $table->decimal('descuento_contado_valor', 12, 2)->default(0)->after('descuento_contado_tipo');
            $table->boolean('descuento_promo_activo')->default(false)->after('descuento_contado_valor');
            $table->string('descuento_promo_tipo')->default('monto')->after('descuento_promo_activo');
            $table->decimal('descuento_promo_valor', 12, 2)->default(0)->after('descuento_promo_tipo');
            $table->string('descuento_promo_nombre')->nullable()->after('descuento_promo_valor');
            $table->text('descuento_promo_descripcion')->nullable()->after('descuento_promo_nombre');
        });
    }

    public function down(): void
    {
        Schema::table('urbanizacion_commercial_settings', function (Blueprint $table): void {
            $table->dropColumn([
                'inicial_minima_usd',
                'plazo_12_habilitado',
                'plazo_24_habilitado',
                'plazo_36_habilitado',
                'descuento_contado_activo',
                'descuento_contado_tipo',
                'descuento_contado_valor',
                'descuento_promo_activo',
                'descuento_promo_tipo',
                'descuento_promo_valor',
                'descuento_promo_nombre',
                'descuento_promo_descripcion',
            ]);
        });
    }
};
