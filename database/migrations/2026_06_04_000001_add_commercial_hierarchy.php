<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grupo_comercial_urbanizacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grupo_comercial_id')->constrained('grupos_comerciales')->cascadeOnDelete();
            $table->foreignId('urbanizacion_id')->constrained('urbanizaciones')->cascadeOnDelete();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->unique(['grupo_comercial_id', 'urbanizacion_id'], 'grupo_urbanizacion_unique');
            $table->index(['urbanizacion_id', 'activo']);
        });

        Schema::create('grupo_comercial_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grupo_comercial_id')->constrained('grupos_comerciales')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('tipo')->default('vendedor');
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->unique(['grupo_comercial_id', 'user_id'], 'grupo_user_unique');
            $table->index(['user_id', 'tipo', 'activo']);
        });

        Schema::table('grupos_comerciales', function (Blueprint $table) {
            $table->text('observaciones')->nullable()->after('descripcion');
        });

        Schema::table('supervisor_profiles', function (Blueprint $table) {
            $table->string('tipo')->default('supervisor_comercial')->after('user_id');
            $table->foreignId('supervisor_comercial_id')->nullable()->after('tipo')->constrained('users')->nullOnDelete();
            $table->foreignId('grupo_comercial_id')->nullable()->after('supervisor_comercial_id')->constrained('grupos_comerciales')->nullOnDelete();
            $table->index(['tipo', 'activo']);
        });

        Schema::table('ventas', function (Blueprint $table) {
            $table->foreignId('urbanizacion_id')->nullable()->after('id')->constrained('urbanizaciones')->nullOnDelete();
            $table->foreignId('vendedor_id')->nullable()->after('reserva_id')->constrained('users')->nullOnDelete();
            $table->foreignId('supervisor_ventas_id')->nullable()->after('vendedor_id')->constrained('users')->nullOnDelete();
            $table->foreignId('supervisor_comercial_id')->nullable()->after('supervisor_ventas_id')->constrained('users')->nullOnDelete();
            $table->foreignId('grupo_comercial_id')->nullable()->after('supervisor_comercial_id')->constrained('grupos_comerciales')->nullOnDelete();
            $table->foreignId('usuario_creador_id')->nullable()->after('grupo_comercial_id')->constrained('users')->nullOnDelete();
            $table->foreignId('usuario_actualizador_id')->nullable()->after('usuario_creador_id')->constrained('users')->nullOnDelete();
            $table->string('tipo_venta')->default('contado')->after('numero_cuotas');
            $table->decimal('monto_total', 12, 2)->default(0)->after('precio_final');
            $table->index(['urbanizacion_id', 'grupo_comercial_id', 'fecha_venta'], 'ventas_commercial_index');
            $table->index(['vendedor_id', 'supervisor_ventas_id'], 'ventas_team_index');
        });

        Schema::table('reservas', function (Blueprint $table) {
            $table->foreignId('urbanizacion_id')->nullable()->after('id')->constrained('urbanizaciones')->nullOnDelete();
            $table->foreignId('vendedor_id')->nullable()->after('usuario_id')->constrained('users')->nullOnDelete();
            $table->foreignId('supervisor_ventas_id')->nullable()->after('vendedor_id')->constrained('users')->nullOnDelete();
            $table->foreignId('supervisor_comercial_id')->nullable()->after('supervisor_ventas_id')->constrained('users')->nullOnDelete();
            $table->foreignId('grupo_comercial_id')->nullable()->after('supervisor_comercial_id')->constrained('grupos_comerciales')->nullOnDelete();
            $table->index(['urbanizacion_id', 'grupo_comercial_id', 'fecha_reserva'], 'reservas_commercial_index');
            $table->index(['vendedor_id', 'supervisor_ventas_id'], 'reservas_team_index');
        });

        $this->backfillCommercialData();
    }

    private function backfillCommercialData(): void
    {
        DB::table('grupos_comerciales')->orderBy('id')->each(function ($grupo): void {
            if ($grupo->supervisor_id) {
                DB::table('grupo_comercial_user')->updateOrInsert(
                    ['grupo_comercial_id' => $grupo->id, 'user_id' => $grupo->supervisor_id],
                    ['tipo' => 'supervisor_comercial', 'activo' => true, 'created_at' => now(), 'updated_at' => now()]
                );
            }
        });

        DB::table('asesores')->orderBy('id')->each(function ($asesor): void {
            if ($asesor->grupo_comercial_id) {
                DB::table('grupo_comercial_user')->updateOrInsert(
                    ['grupo_comercial_id' => $asesor->grupo_comercial_id, 'user_id' => $asesor->user_id],
                    ['tipo' => 'vendedor', 'activo' => (bool) $asesor->activo, 'created_at' => now(), 'updated_at' => now()]
                );
            }
        });

        DB::table('ventas')->orderBy('id')->each(function ($venta): void {
            $urbanizacionId = DB::table('lotes')
                ->join('manzanos', 'manzanos.id', '=', 'lotes.manzano_id')
                ->where('lotes.id', $venta->lote_id)
                ->value('manzanos.urbanizacion_id');
            $asesor = DB::table('asesores')->where('user_id', $venta->user_id)->first();

            DB::table('ventas')->where('id', $venta->id)->update([
                'urbanizacion_id' => $urbanizacionId,
                'vendedor_id' => $asesor?->user_id,
                'supervisor_ventas_id' => $asesor?->supervisor_id,
                'grupo_comercial_id' => $asesor?->grupo_comercial_id,
                'usuario_creador_id' => $venta->user_id,
                'tipo_venta' => ((int) $venta->numero_cuotas) > 0 ? 'credito' : 'contado',
                'monto_total' => $venta->precio_final,
            ]);
        });

        DB::table('reservas')->orderBy('id')->each(function ($reserva): void {
            $urbanizacionId = DB::table('lotes')
                ->join('manzanos', 'manzanos.id', '=', 'lotes.manzano_id')
                ->where('lotes.id', $reserva->lote_id)
                ->value('manzanos.urbanizacion_id');
            $asesor = DB::table('asesores')->where('user_id', $reserva->usuario_id)->first();

            DB::table('reservas')->where('id', $reserva->id)->update([
                'urbanizacion_id' => $urbanizacionId,
                'vendedor_id' => $asesor?->user_id,
                'supervisor_ventas_id' => $asesor?->supervisor_id,
                'grupo_comercial_id' => $asesor?->grupo_comercial_id,
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->dropIndex('reservas_commercial_index');
            $table->dropIndex('reservas_team_index');
            $table->dropConstrainedForeignId('grupo_comercial_id');
            $table->dropConstrainedForeignId('supervisor_comercial_id');
            $table->dropConstrainedForeignId('supervisor_ventas_id');
            $table->dropConstrainedForeignId('vendedor_id');
            $table->dropConstrainedForeignId('urbanizacion_id');
        });
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropIndex('ventas_commercial_index');
            $table->dropIndex('ventas_team_index');
            foreach (['usuario_actualizador_id', 'usuario_creador_id', 'grupo_comercial_id', 'supervisor_comercial_id', 'supervisor_ventas_id', 'vendedor_id', 'urbanizacion_id'] as $column) {
                $table->dropConstrainedForeignId($column);
            }
            $table->dropColumn(['tipo_venta', 'monto_total']);
        });
        Schema::table('supervisor_profiles', function (Blueprint $table) {
            $table->dropIndex(['tipo', 'activo']);
            $table->dropConstrainedForeignId('grupo_comercial_id');
            $table->dropConstrainedForeignId('supervisor_comercial_id');
            $table->dropColumn('tipo');
        });
        Schema::table('grupos_comerciales', fn (Blueprint $table) => $table->dropColumn('observaciones'));
        Schema::dropIfExists('grupo_comercial_user');
        Schema::dropIfExists('grupo_comercial_urbanizacion');
    }
};
