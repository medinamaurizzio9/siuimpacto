<?php

use App\Services\CommercialSettingsService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('urbanizacion_commercial_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('urbanizacion_id')->unique()->constrained('urbanizaciones')->cascadeOnDelete();
            $table->unsignedInteger('reserva_dias_habiles_asesor')->default(5);
            $table->decimal('tipo_cambio_usd_bs', 12, 2)->default(6.96);
            $table->string('incremento_credito_tipo')->default('monto');
            $table->decimal('incremento_credito_valor', 12, 2)->default(0);
            $table->timestamps();
        });

        $global = DB::table('commercial_settings')->pluck('value', 'key');
        $now = now();

        DB::table('urbanizaciones')->orderBy('id')->pluck('id')->each(function (int $urbanizacionId) use ($global, $now): void {
            DB::table('urbanizacion_commercial_settings')->insert([
                'urbanizacion_id' => $urbanizacionId,
                'reserva_dias_habiles_asesor' => max(1, (int) ($global[CommercialSettingsService::RESERVA_DIAS_HABILES_ASESOR] ?? 5)),
                'tipo_cambio_usd_bs' => max(0, (float) ($global[CommercialSettingsService::TIPO_CAMBIO_USD_BS] ?? 6.96)),
                'incremento_credito_tipo' => in_array(($global[CommercialSettingsService::INCREMENTO_CREDITO_TIPO] ?? 'monto'), ['monto', 'porcentaje'], true)
                    ? $global[CommercialSettingsService::INCREMENTO_CREDITO_TIPO]
                    : 'monto',
                'incremento_credito_valor' => max(0, (float) ($global[CommercialSettingsService::INCREMENTO_CREDITO_VALOR] ?? 0)),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('urbanizacion_commercial_settings');
    }
};
