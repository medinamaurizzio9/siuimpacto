<?php

namespace Tests\Feature;

use App\Models\Lote;
use App\Models\Urbanizacion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UrbanizacionContextTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_puede_ver_todas_las_urbanizaciones_activas_para_seleccionar(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@impacto.test')->firstOrFail();
        $urbanizaciones = Urbanizacion::where('estado', 'activa')->pluck('nombre');

        $response = $this->actingAs($admin)->get(route('urbanizaciones.select'));

        $response->assertOk();
        foreach ($urbanizaciones as $nombre) {
            $response->assertSee($nombre);
        }
    }

    public function test_vendedor_solo_ve_urbanizaciones_asignadas(): void
    {
        $this->seed();

        $vendedor = User::where('email', 'vendedor@impacto.test')->firstOrFail();
        $asignada = $vendedor->urbanizacionesAsignadas()->firstOrFail();
        $noAsignada = Urbanizacion::whereKeyNot($asignada->id)->where('estado', 'activa')->firstOrFail();

        $this->actingAs($vendedor)
            ->get(route('urbanizaciones.select'))
            ->assertOk()
            ->assertSee($asignada->nombre)
            ->assertDontSee($noAsignada->nombre);
    }

    public function test_vendedor_no_puede_vender_lote_de_urbanizacion_no_asignada(): void
    {
        $this->seed();

        $vendedor = User::where('email', 'vendedor@impacto.test')->firstOrFail();
        $asignada = $vendedor->urbanizacionesAsignadas()->firstOrFail();
        $loteNoAsignado = Lote::whereHas('manzano', fn ($query) => $query->where('urbanizacion_id', '!=', $asignada->id))
            ->where('estado', 'disponible')
            ->firstOrFail();

        $this->actingAs($vendedor)
            ->withSession(['urbanizacion_id' => $asignada->id])
            ->post(route('ventas.store'), [
                'lote_id' => $loteNoAsignado->id,
                'cliente_id' => \App\Models\Cliente::firstOrFail()->id,
                'fecha_venta' => now()->format('Y-m-d'),
                'precio_final' => $loteNoAsignado->precio,
                'cuota_inicial' => 0,
                'numero_cuotas' => 0,
                'estado' => 'activa',
                'metodo_pago' => 'efectivo',
            ])
            ->assertForbidden();
    }

    public function test_cliente_externo_no_ve_caja_ni_ventas_internas(): void
    {
        $this->seed();

        $cliente = User::where('email', 'cliente@impacto.test')->firstOrFail();

        $this->actingAs($cliente)
            ->get(route('clientes.mi-cuenta'))
            ->assertOk()
            ->assertDontSee('Caja')
            ->assertDontSee('Ventas');

        $this->actingAs($cliente)->get(route('caja.index'))->assertForbidden();
    }

    public function test_vista_publica_muestra_solo_disponibilidad(): void
    {
        $this->seed();

        $urbanizacion = Urbanizacion::firstOrFail();

        $this->get(route('disponibilidad.publica', ['urbanizacion_id' => $urbanizacion->id]))
            ->assertOk()
            ->assertSee('Disponibilidad')
            ->assertSee('Consultar con asesor')
            ->assertDontSee('Caja')
            ->assertDontSee('Ventas internas');
    }

    public function test_sistema_redirige_a_seleccionar_urbanizacion_si_no_hay_sesion(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@impacto.test')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertRedirect(route('urbanizaciones.select'));
    }
}
