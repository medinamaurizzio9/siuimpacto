<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\CashMovement;
use App\Models\Cliente;
use App\Models\Lote;
use App\Models\Urbanizacion;
use App\Models\User;
use App\Services\LotCsvImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FinalHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_permisos_por_accion_impiden_crear_lotes_a_vendedor(): void
    {
        $this->seed();

        $vendedor = User::where('email', 'vendedor@impacto.test')->firstOrFail();

        $this->actingAs($vendedor)
            ->withSession(['urbanizacion_id' => $vendedor->urbanizacionesAsignadas()->firstOrFail()->id])
            ->get(route('lotes.create'))
            ->assertForbidden();
    }

    public function test_auditoria_registrada_al_anular_caja(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@impacto.test')->firstOrFail();
        $movimiento = CashMovement::firstOrFail();
        $urbanizacionId = $movimiento->venta?->lote->manzano->urbanizacion_id
            ?? $movimiento->reserva?->lote->manzano->urbanizacion_id
            ?? $movimiento->cuota?->venta->lote->manzano->urbanizacion_id;

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacionId])
            ->post(route('caja.annul', $movimiento), ['motivo' => 'Correccion de prueba'])
            ->assertRedirect();

        $this->assertDatabaseHas('audit_logs', [
            'modelo' => 'CashMovement',
            'modelo_id' => $movimiento->id,
            'accion' => 'anular_caja',
        ]);
    }

    public function test_importacion_csv_con_datos_validos(): void
    {
        $this->seed();

        $path = $this->csvPath('urbanizacion,manzano,lote,superficie_m2,precio_m2,precio_total,cuota_inicial_tipo,cuota_inicial_valor,estado,coord_x,coord_y,observaciones
IMPACTO CSV,Z,01,300,60,18000,porcentaje,15,disponible,25,30,Importado');

        $result = app(LotCsvImportService::class)->parse($path);
        $this->assertSame([], $result['errors']);
        $this->assertSame(1, app(LotCsvImportService::class)->import($result['rows']));
        $this->assertDatabaseHas('lotes', [
            'codigo' => '01',
            'cuota_inicial_tipo' => 'porcentaje',
            'cuota_inicial_valor' => 15,
        ]);
    }

    public function test_importacion_csv_antigua_sigue_funcionando_con_cuota_inicial_por_defecto(): void
    {
        $this->seed();

        $path = $this->csvPath('urbanizacion,manzano,lote,superficie_m2,precio_m2,precio_total,estado,coord_x,coord_y,observaciones
IMPACTO CSV LEGACY,Z,02,300,60,18000,disponible,25,30,Importado');

        $result = app(LotCsvImportService::class)->parse($path);
        $this->assertSame([], $result['errors']);
        $this->assertSame(1, app(LotCsvImportService::class)->import($result['rows']));
        $this->assertDatabaseHas('lotes', [
            'codigo' => '02',
            'cuota_inicial_tipo' => 'monto',
            'cuota_inicial_valor' => 0,
        ]);
    }

    public function test_importacion_csv_rechaza_duplicados(): void
    {
        $this->seed();

        $lote = Lote::with('manzano.urbanizacion')->firstOrFail();
        $path = $this->csvPath("urbanizacion,manzano,lote,superficie_m2,precio_m2,precio_total,estado,coord_x,coord_y,observaciones\n{$lote->manzano->urbanizacion->nombre},{$lote->manzano->codigo},{$lote->codigo},300,60,18000,disponible,25,30,Duplicado");

        $result = app(LotCsvImportService::class)->parse($path);
        $this->assertNotEmpty($result['errors']);
    }

    public function test_confirmacion_de_importacion_usa_filas_guardadas_en_sesion(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@impacto.test')->firstOrFail();
        $urbanizacionId = Urbanizacion::firstOrFail()->id;
        $path = $this->csvPath('urbanizacion,manzano,lote,superficie_m2,precio_m2,precio_total,cuota_inicial_tipo,cuota_inicial_valor,estado,coord_x,coord_y,observaciones
IMPACTO CSV SESION,S,10,300,60,18000,monto,5000,disponible,25,30,Importado');

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacionId])
            ->post(route('lotes.import.preview'), [
                'csv' => new UploadedFile($path, 'lotes.csv', 'text/csv', null, true),
            ])
            ->assertOk()
            ->assertSessionHas('lotes_import_rows');

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacionId])
            ->post(route('lotes.import.store'))
            ->assertRedirect(route('lotes.index'))
            ->assertSessionHas('status', 'Se importaron 1 lotes correctamente.');

        $this->assertDatabaseHas('lotes', [
            'codigo' => '10',
            'cuota_inicial_tipo' => 'monto',
            'cuota_inicial_valor' => 5000,
        ]);
    }

    public function test_confirmacion_de_importacion_sin_filas_redirige_con_mensaje_claro(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@impacto.test')->firstOrFail();
        $urbanizacionId = Urbanizacion::firstOrFail()->id;

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacionId])
            ->post(route('lotes.import.store'))
            ->assertRedirect(route('lotes.import.create'))
            ->assertSessionHasErrors();
    }

    public function test_backup_command_se_ejecuta(): void
    {
        $this->artisan('impacto:backup')->assertSuccessful();
    }

    public function test_usuario_sin_permiso_no_ve_boton_critico(): void
    {
        $this->seed();

        $vendedor = User::where('email', 'vendedor@impacto.test')->firstOrFail();

        $this->actingAs($vendedor)
            ->withSession(['urbanizacion_id' => $vendedor->urbanizacionesAsignadas()->firstOrFail()->id])
            ->get(route('caja.index'))
            ->assertForbidden();
    }

    private function csvPath(string $contents): string
    {
        $path = storage_path('framework/testing-lotes.csv');
        file_put_contents($path, $contents);

        return $path;
    }
}
