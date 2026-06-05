<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\GrupoComercial;
use App\Models\Lote;
use App\Models\Urbanizacion;
use App\Models\User;
use App\Models\Venta;
use App\Services\SaleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommercialHierarchyTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_administrador_puede_asignar_urbanizaciones_a_grupo_y_se_audita(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@impacto.test')->firstOrFail();
        $grupo = GrupoComercial::firstOrFail();
        $urbanizaciones = Urbanizacion::pluck('id')->all();

        $this->actingAs($admin)
            ->put(route('grupos-comerciales.asignaciones.update', $grupo), ['urbanizaciones' => $urbanizaciones])
            ->assertRedirect(route('grupos-comerciales.show', $grupo));

        $this->assertCount(count($urbanizaciones), $grupo->fresh()->urbanizaciones);
        $this->assertDatabaseHas('audit_logs', [
            'modelo' => 'GrupoComercial',
            'modelo_id' => $grupo->id,
            'accion' => 'asignar_urbanizaciones_grupo',
        ]);
    }

    public function test_administrador_sin_rol_super_no_puede_asignar_urbanizaciones_a_grupo(): void
    {
        $this->seed();
        $admin = User::factory()->create(['email' => 'admin.limitado@test.local']);
        $admin->assignRole('administrador');

        $this->actingAs($admin)
            ->put(route('grupos-comerciales.asignaciones.update', GrupoComercial::firstOrFail()), [
                'urbanizaciones' => Urbanizacion::pluck('id')->all(),
            ])
            ->assertForbidden();
    }

    public function test_vendedor_guarda_jerarquia_comercial_en_venta(): void
    {
        $this->seed();
        $vendedor = User::where('email', 'vendedor@impacto.test')->firstOrFail();
        $urbanizacion = $vendedor->urbanizacionesAsignadas()->firstOrFail();
        $cliente = Cliente::where('urbanizacion_id', $urbanizacion->id)->firstOrFail();
        $lote = Lote::whereHas('manzano', fn ($query) => $query->where('urbanizacion_id', $urbanizacion->id))
            ->where('estado', 'disponible')->firstOrFail();

        $venta = app(SaleService::class)->create([
            'lote_id' => $lote->id,
            'cliente_id' => $cliente->id,
            'fecha_venta' => now()->toDateString(),
            'precio_final' => $lote->precio,
            'cuota_inicial' => 0,
            'numero_cuotas' => 0,
            'estado' => 'activa',
            'metodo_pago' => 'efectivo',
        ], $vendedor);

        $asesor = $vendedor->asesor;
        $this->assertSame($urbanizacion->id, $venta->urbanizacion_id);
        $this->assertSame($vendedor->id, $venta->vendedor_id);
        $this->assertSame($asesor->supervisor_id, $venta->supervisor_ventas_id);
        $this->assertSame($asesor->grupo_comercial_id, $venta->grupo_comercial_id);
        $this->assertSame('contado', $venta->tipo_venta);
        $this->assertEquals($venta->precio_final, $venta->monto_total);
    }

    public function test_supervisor_comercial_solo_accede_a_urbanizaciones_asignadas(): void
    {
        $this->seed();
        $supervisor = User::where('email', 'supervisor@impacto.test')->firstOrFail();
        $asignada = $supervisor->urbanizacionesAsignadas()->firstOrFail();
        $ajena = Urbanizacion::whereKeyNot($asignada->id)->firstOrFail();

        $this->assertTrue(\App\Support\UrbanizacionContext::userCanAccess($supervisor, $asignada->id));
        $this->assertFalse(\App\Support\UrbanizacionContext::userCanAccess($supervisor, $ajena->id));
    }

    public function test_reporte_comercial_respeta_acceso_y_exporta(): void
    {
        $this->seed();
        $supervisor = User::where('email', 'supervisor@impacto.test')->firstOrFail();
        $urbanizacion = $supervisor->urbanizacionesAsignadas()->firstOrFail();

        $this->actingAs($supervisor)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->get(route('reportes.comercial'))
            ->assertOk()
            ->assertSee('Reporte comercial');

        $this->actingAs($supervisor)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->get(route('reportes.comercial.pdf'))
            ->assertOk();
    }

    public function test_grupo_comercial_muestra_indicadores_y_detalle(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@impacto.test')->firstOrFail();
        $grupo = GrupoComercial::firstOrFail();

        $this->actingAs($admin)->get(route('grupos-comerciales.index'))
            ->assertOk()
            ->assertSee('Monto vendido')
            ->assertSee('Reservas activas');

        $this->actingAs($admin)->get(route('grupos-comerciales.show', $grupo))
            ->assertOk()
            ->assertSee('Datos generales')
            ->assertSee('Ventas recientes');
    }
}
