<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Lote;
use App\Models\Manzano;
use App\Models\Reserva;
use App\Models\Urbanizacion;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Testing\TestResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListadoFiltrosPaginacionTest extends TestCase
{
    use RefreshDatabase;

    public function test_clientes_tienen_filtros_y_paginacion(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@impacto.test')->firstOrFail();
        $urbanizacion = Urbanizacion::where('estado', 'activa')->firstOrFail();

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->get(route('clientes.index', ['q' => 'Mariela', 'ventas' => 'con_ventas', 'per_page' => 10]))
            ->assertOk()
            ->assertSee('Buscar cliente')
            ->assertSee('name="q"', false)
            ->assertSee('name="ventas"', false)
            ->assertSee('name="per_page"', false)
            ->assertSee('Filtrar')
            ->assertSee('Limpiar')
            ->assertSee('Mostrando')
            ->assertSee('Mariela');
    }

    public function test_listados_principales_paginan_50_por_defecto(): void
    {
        [$admin, $urbanizacion] = $this->seedContext();

        $responses = [
            $this->actingAs($admin)->withSession(['urbanizacion_id' => $urbanizacion->id])->get(route('lotes.index')),
            $this->actingAs($admin)->withSession(['urbanizacion_id' => $urbanizacion->id])->get(route('clientes.index')),
            $this->actingAs($admin)->withSession(['urbanizacion_id' => $urbanizacion->id])->get(route('reservas.index')),
            $this->actingAs($admin)->withSession(['urbanizacion_id' => $urbanizacion->id])->get(route('ventas.index')),
            $this->actingAs($admin)->withSession(['urbanizacion_id' => $urbanizacion->id])->get(route('manzanos.index')),
            $this->actingAs($admin)->withSession(['urbanizacion_id' => $urbanizacion->id])->get(route('admin.usuarios')),
        ];

        foreach ($responses as $response) {
            $response->assertOk();
        }

        $this->assertSame(50, $responses[0]->viewData('lotes')->perPage());
        $this->assertSame(50, $responses[1]->viewData('clientes')->perPage());
        $this->assertSame(50, $responses[2]->viewData('reservas')->perPage());
        $this->assertSame(50, $responses[3]->viewData('ventas')->perPage());
        $this->assertSame(50, $responses[4]->viewData('manzanos')->perPage());
        $this->assertSame(50, $responses[5]->viewData('users')->perPage());
    }

    public function test_ordenamiento_ascendente_y_descendente_funciona_en_lotes(): void
    {
        [$admin, $urbanizacion] = $this->seedContext();
        $manzano = Manzano::where('urbanizacion_id', $urbanizacion->id)->firstOrFail();

        Lote::create([
            'manzano_id' => $manzano->id,
            'codigo' => 'AAA-ORDEN',
            'superficie' => 100,
            'precio' => 1000,
            'estado' => 'disponible',
        ]);
        Lote::create([
            'manzano_id' => $manzano->id,
            'codigo' => 'ZZZ-ORDEN',
            'superficie' => 100,
            'precio' => 1000,
            'estado' => 'disponible',
        ]);

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->get(route('lotes.index', ['buscar' => 'ORDEN', 'sort' => 'codigo', 'direction' => 'asc']))
            ->assertOk()
            ->assertSeeInOrder(['AAA-ORDEN', 'ZZZ-ORDEN']);

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->get(route('lotes.index', ['buscar' => 'ORDEN', 'sort' => 'codigo', 'direction' => 'desc']))
            ->assertOk()
            ->assertSeeInOrder(['ZZZ-ORDEN', 'AAA-ORDEN']);
    }

    public function test_filtros_se_mantienen_al_ordenar(): void
    {
        [$admin, $urbanizacion] = $this->seedContext();

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->get(route('clientes.index', ['q' => 'Mariela', 'ventas' => 'con_ventas']))
            ->assertOk()
            ->assertSee('q=Mariela', false)
            ->assertSee('ventas=con_ventas', false)
            ->assertSee('sort=nombre', false)
            ->assertSee('direction=asc', false);
    }

    public function test_ordenamiento_por_campos_relacionados_funciona(): void
    {
        [$admin, $urbanizacion] = $this->seedContext();
        $manzano = Manzano::where('urbanizacion_id', $urbanizacion->id)->firstOrFail();
        $loteA = Lote::create(['manzano_id' => $manzano->id, 'codigo' => 'L-A-REL', 'superficie' => 100, 'precio' => 1000, 'estado' => 'disponible']);
        $loteZ = Lote::create(['manzano_id' => $manzano->id, 'codigo' => 'L-Z-REL', 'superficie' => 100, 'precio' => 1000, 'estado' => 'disponible']);
        $clienteA = Cliente::create(['urbanizacion_id' => $urbanizacion->id, 'nombre' => 'Ana Orden Relacion', 'documento' => 'REL-A']);
        $clienteZ = Cliente::create(['urbanizacion_id' => $urbanizacion->id, 'nombre' => 'Zoe Orden Relacion', 'documento' => 'REL-Z']);

        Reserva::create(['cliente_id' => $clienteZ->id, 'lote_id' => $loteZ->id, 'usuario_id' => $admin->id, 'fecha_reserva' => now(), 'fecha_vencimiento' => now()->addDay(), 'monto_reserva' => 100, 'estado' => 'activa', 'tipo_operacion' => 'contado']);
        Reserva::create(['cliente_id' => $clienteA->id, 'lote_id' => $loteA->id, 'usuario_id' => $admin->id, 'fecha_reserva' => now(), 'fecha_vencimiento' => now()->addDay(), 'monto_reserva' => 100, 'estado' => 'activa', 'tipo_operacion' => 'contado']);
        Venta::create(['cliente_id' => $clienteZ->id, 'lote_id' => $loteZ->id, 'user_id' => $admin->id, 'fecha_venta' => now(), 'precio_final' => 1000, 'precio_final_usd' => 1000, 'cuota_inicial' => 100, 'saldo_financiar' => 900, 'numero_cuotas' => 1, 'estado' => 'activa', 'tipo_operacion' => 'contado']);
        Venta::create(['cliente_id' => $clienteA->id, 'lote_id' => $loteA->id, 'user_id' => $admin->id, 'fecha_venta' => now(), 'precio_final' => 1000, 'precio_final_usd' => 1000, 'cuota_inicial' => 100, 'saldo_financiar' => 900, 'numero_cuotas' => 1, 'estado' => 'activa', 'tipo_operacion' => 'contado']);

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->get(route('reservas.index', ['sort' => 'cliente', 'direction' => 'asc']))
            ->assertOk()
            ->assertSeeInOrder(['Ana Orden Relacion', 'Zoe Orden Relacion']);

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->get(route('ventas.index', ['sort' => 'cliente', 'direction' => 'desc']))
            ->assertOk()
            ->assertSeeInOrder(['Zoe Orden Relacion', 'Ana Orden Relacion']);
    }

    public function test_ventas_pagina_resultados(): void
    {
        $this->getAsAdmin(route('ventas.index', ['per_page' => 15]))
            ->assertOk()
            ->assertSee('Buscar venta')
            ->assertSee('Mostrando')
            ->assertSee('ventas');
    }

    public function test_ventas_filtra_por_cliente(): void
    {
        $this->getAsAdmin(route('ventas.index', ['q' => 'Carlos', 'per_page' => 15]))
            ->assertOk()
            ->assertSee('Carlos Alberto Rojas Perez')
            ->assertDontSee('Mariela Fernandez Rojas');
    }

    public function test_ventas_filtra_por_estado(): void
    {
        $this->getAsAdmin(route('ventas.index', ['estado' => 'completada', 'per_page' => 15]))
            ->assertOk()
            ->assertSee('name="q"', false)
            ->assertSee('name="estado"', false)
            ->assertSee('name="fecha_desde"', false)
            ->assertSee('name="fecha_hasta"', false)
            ->assertSee('name="per_page"', false)
            ->assertSee('Filtrar')
            ->assertSee('Limpiar')
            ->assertSee('completada');
    }

    public function test_ventas_filtra_por_fecha(): void
    {
        $this->getAsAdmin(route('ventas.index', ['fecha_desde' => now()->subDays(5)->toDateString(), 'per_page' => 15]))
            ->assertOk()
            ->assertSee('Mostrando')
            ->assertDontSee('Carlos Alberto Rojas Perez');
    }

    public function test_caja_pagina_resultados(): void
    {
        $this->getAsAdmin(route('caja.index', ['per_page' => 15]))
            ->assertOk()
            ->assertSee('Buscar movimiento')
            ->assertSee('Mostrando')
            ->assertSee('movimientos')
            ->assertSee('Imprimir recibo');
    }

    public function test_caja_filtra_por_cliente(): void
    {
        $this->getAsAdmin(route('caja.index', ['q' => 'Carlos', 'per_page' => 15]))
            ->assertOk()
            ->assertSee('Carlos Alberto Rojas Perez')
            ->assertDontSee('Mariela Fernandez Rojas');
    }

    public function test_caja_filtra_por_concepto(): void
    {
        $this->getAsAdmin(route('caja.index', ['concepto' => 'cuota', 'per_page' => 15]))
            ->assertOk()
            ->assertSee('cuota')
            ->assertSee('REC-CUOTA');
    }

    public function test_caja_filtra_por_metodo(): void
    {
        $this->getAsAdmin(route('caja.index', ['metodo_pago' => 'QR', 'per_page' => 15]))
            ->assertOk()
            ->assertSee('QR')
            ->assertSee('QR-ANT-102');
    }

    public function test_caja_filtra_por_fecha(): void
    {
        $this->getAsAdmin(route('caja.index', ['fecha_desde' => now()->subDays(2)->toDateString(), 'per_page' => 15]))
            ->assertOk()
            ->assertSee('Mostrando')
            ->assertDontSee('QR-ANT-102');
    }

    public function test_menu_finanzas_no_muestra_recibos(): void
    {
        $this->getAsAdmin(route('dashboard'))
            ->assertOk()
            ->assertSee('Finanzas')
            ->assertSee('Caja')
            ->assertDontSee('Recibos');
    }

    public function test_vendedor_no_ve_boton_anular_en_caja(): void
    {
        $this->seed();

        $vendedor = User::where('email', 'vendedor@impacto.test')->firstOrFail();

        $this->actingAs($vendedor)
            ->withSession(['urbanizacion_id' => $vendedor->urbanizacionesAsignadas()->firstOrFail()->id])
            ->get(route('caja.index'))
            ->assertForbidden()
            ->assertDontSee('Anular');
    }

    public function test_admin_si_ve_boton_anular_en_caja(): void
    {
        $this->getAsAdmin(route('caja.index'))
            ->assertOk()
            ->assertSee('Anular');
    }

    public function test_export_ventas_respeta_filtros(): void
    {
        $response = $this->getAsAdmin(route('export.csv', ['tipo' => 'ventas', 'q' => 'Carlos']));

        $response->assertOk();
        $response->assertSee('Carlos Alberto Rojas Perez');
        $response->assertDontSee('Mariela Fernandez Rojas');
    }

    public function test_export_caja_respeta_filtros(): void
    {
        $response = $this->getAsAdmin(route('export.csv', ['tipo' => 'caja', 'metodo_pago' => 'QR']));

        $response->assertOk();
        $response->assertSee('QR');
        $response->assertDontSee('banco');
    }

    private function getAsAdmin(string $url): TestResponse
    {
        [$admin, $urbanizacion] = $this->seedContext();

        return $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->get($url);
    }

    private function seedContext(): array
    {
        $this->seed();

        return [
            User::where('email', 'admin@impacto.test')->firstOrFail(),
            Urbanizacion::where('estado', 'activa')->firstOrFail(),
        ];
    }
}
