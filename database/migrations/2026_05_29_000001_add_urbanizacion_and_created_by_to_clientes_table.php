<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            if (! Schema::hasColumn('clientes', 'urbanizacion_id')) {
                $table->foreignId('urbanizacion_id')->nullable()->after('id')->constrained('urbanizaciones')->nullOnDelete();
            }

            if (! Schema::hasColumn('clientes', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('urbanizacion_id')->constrained('users')->nullOnDelete();
            }
        });

        DB::table('clientes')->where('documento', '')->update(['documento' => null]);
        $this->backfillUrbanizacionAndCreator();
        $this->mergeDuplicates();

        Schema::table('clientes', function (Blueprint $table) {
            $table->unique(['documento', 'urbanizacion_id'], 'clientes_documento_urbanizacion_unique');
            $table->index(['urbanizacion_id', 'nombre'], 'clientes_urbanizacion_nombre_index');
            $table->index('created_by', 'clientes_created_by_index');
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropUnique('clientes_documento_urbanizacion_unique');
            $table->dropIndex('clientes_urbanizacion_nombre_index');
            $table->dropIndex('clientes_created_by_index');
            $table->dropConstrainedForeignId('created_by');
            $table->dropConstrainedForeignId('urbanizacion_id');
        });
    }

    private function backfillUrbanizacionAndCreator(): void
    {
        $defaultUrbanizacionId = DB::table('urbanizaciones')->orderBy('id')->value('id');
        $defaultUserId = DB::table('users')->orderBy('id')->value('id');

        DB::table('clientes')->orderBy('id')->each(function (object $cliente) use ($defaultUrbanizacionId, $defaultUserId): void {
            $venta = DB::table('ventas')
                ->join('lotes', 'ventas.lote_id', '=', 'lotes.id')
                ->join('manzanos', 'lotes.manzano_id', '=', 'manzanos.id')
                ->where('ventas.cliente_id', $cliente->id)
                ->select('manzanos.urbanizacion_id', 'ventas.user_id')
                ->orderBy('ventas.id')
                ->first();

            $reserva = DB::table('reservas')
                ->join('lotes', 'reservas.lote_id', '=', 'lotes.id')
                ->join('manzanos', 'lotes.manzano_id', '=', 'manzanos.id')
                ->where('reservas.cliente_id', $cliente->id)
                ->select('manzanos.urbanizacion_id', 'reservas.usuario_id')
                ->orderBy('reservas.id')
                ->first();

            DB::table('clientes')
                ->where('id', $cliente->id)
                ->update([
                    'urbanizacion_id' => $cliente->urbanizacion_id ?? $venta?->urbanizacion_id ?? $reserva?->urbanizacion_id ?? $defaultUrbanizacionId,
                    'created_by' => $cliente->created_by ?? $venta?->user_id ?? $reserva?->usuario_id ?? $defaultUserId,
                ]);
        });
    }

    private function mergeDuplicates(): void
    {
        DB::table('clientes')
            ->select('documento', 'urbanizacion_id', DB::raw('MIN(id) as keeper_id'), DB::raw('COUNT(*) as total'))
            ->whereNotNull('documento')
            ->where('documento', '!=', '')
            ->whereNotNull('urbanizacion_id')
            ->groupBy('documento', 'urbanizacion_id')
            ->having('total', '>', 1)
            ->orderBy('keeper_id')
            ->each(function (object $group): void {
                $duplicateIds = DB::table('clientes')
                    ->where('documento', $group->documento)
                    ->where('urbanizacion_id', $group->urbanizacion_id)
                    ->where('id', '!=', $group->keeper_id)
                    ->pluck('id');

                foreach (['ventas', 'reservas', 'cash_movements', 'users'] as $table) {
                    if (Schema::hasTable($table) && Schema::hasColumn($table, 'cliente_id')) {
                        DB::table($table)->whereIn('cliente_id', $duplicateIds)->update(['cliente_id' => $group->keeper_id]);
                    }
                }

                DB::table('clientes')->whereIn('id', $duplicateIds)->delete();
            });
    }
};
