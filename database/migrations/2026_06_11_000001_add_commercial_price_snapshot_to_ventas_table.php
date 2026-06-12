<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            if (! Schema::hasColumn('ventas', 'tipo_operacion')) {
                $table->string('tipo_operacion')->nullable()->after('reserva_id');
            }
            if (! Schema::hasColumn('ventas', 'precio_base_usd')) {
                $table->decimal('precio_base_usd', 12, 2)->nullable()->after('tipo_operacion');
            }
            if (! Schema::hasColumn('ventas', 'incremento_credito_aplicado')) {
                $table->decimal('incremento_credito_aplicado', 12, 2)->nullable()->after('precio_base_usd');
            }
            if (! Schema::hasColumn('ventas', 'precio_final_usd')) {
                $table->decimal('precio_final_usd', 12, 2)->nullable()->after('precio_final');
            }
            if (! Schema::hasColumn('ventas', 'precio_final_bs')) {
                $table->decimal('precio_final_bs', 12, 2)->nullable()->after('precio_final_usd');
            }
            if (! Schema::hasColumn('ventas', 'tipo_cambio_usd_bs')) {
                $table->decimal('tipo_cambio_usd_bs', 12, 2)->nullable()->after('precio_final_bs');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropColumn([
                'tipo_operacion',
                'precio_base_usd',
                'incremento_credito_aplicado',
                'precio_final_usd',
                'precio_final_bs',
                'tipo_cambio_usd_bs',
            ]);
        });
    }
};
