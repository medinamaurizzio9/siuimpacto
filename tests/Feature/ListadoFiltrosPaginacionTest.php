<?php

namespace Tests\Feature;

use App\Models\Urbanizacion;
use App\Models\User;
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
            ->assertSee('pagination', false)
            ->assertSee('Mariela');
    }

    public function test_ventas_pagina_resultados(): void
    {
        $this->getAsAdmin(route('ventas.index', ['per_page' => 15]))
            ->assertOk()
            ->assertSee('Buscar venta')
            ->assertSee('Mostrando')
            ->assertSee('ventas')
            ->assertSee('pagination', false);
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
            ->assertSee('Imprimir recibo')
            ->assertSee('pagination', false);
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
        $this->seed();

        $admin = User::where('email', 'admin@impacto.test')->firstOrFail();
        $urbanizacion = Urbanizacion::where('estado', 'activa')->firstOrFail();

        return $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->get($url);
    }
}
