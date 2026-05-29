<?php

namespace Tests\Feature;

use App\Models\Lote;
use App\Models\Manzano;
use App\Models\Urbanizacion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportesSeparadosTest extends TestCase
{
    use RefreshDatabase;

    public function test_reportes_muestra_solo_indice_resumen(): void
    {
        [$admin, $urbanizacion] = $this->adminConUrbanizacion();

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->get(route('reportes.index'))
            ->assertOk()
            ->assertSee('Reportes')
            ->assertSee('Lotes por estado')
            ->assertDontSee('data-report="lotes-estado"', false)
            ->assertDontSee('data-report="reservas"', false)
            ->assertDontSee('data-report="cuotas"', false)
            ->assertDontSee('data-report="ingresos"', false);
    }

    public function test_lotes_estado_muestra_lotes_y_no_cuotas(): void
    {
        [$admin, $urbanizacion] = $this->adminConUrbanizacion();

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->get(route('reportes.lotes-estado'))
            ->assertOk()
            ->assertSee('data-report="lotes-estado"', false)
            ->assertSee('Tabla de lotes')
            ->assertDontSee('data-report="cuotas"', false);
    }

    public function test_reservas_muestra_reservas_y_no_cuotas(): void
    {
        [$admin, $urbanizacion] = $this->adminConUrbanizacion();

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->get(route('reportes.reservas'))
            ->assertOk()
            ->assertSee('data-report="reservas"', false)
            ->assertSee('Tabla de reservas')
            ->assertDontSee('data-report="cuotas"', false);
    }

    public function test_cuotas_muestra_cuotas_y_no_reservas(): void
    {
        [$admin, $urbanizacion] = $this->adminConUrbanizacion();

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->get(route('reportes.cuotas'))
            ->assertOk()
            ->assertSee('data-report="cuotas"', false)
            ->assertSee('Tabla de cuotas')
            ->assertDontSee('data-report="reservas"', false);
    }

    public function test_ingresos_muestra_ingresos_y_no_lotes_por_estado(): void
    {
        [$admin, $urbanizacion] = $this->adminConUrbanizacion();

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->get(route('reportes.ingresos'))
            ->assertOk()
            ->assertSee('data-report="ingresos"', false)
            ->assertSee('Tabla de ingresos')
            ->assertDontSee('data-report="lotes-estado"', false);
    }

    public function test_estado_cuenta_sin_cliente_no_carga_datos(): void
    {
        [$admin, $urbanizacion] = $this->adminConUrbanizacion();

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->get(route('reportes.estado-cuenta'))
            ->assertOk()
            ->assertSee('data-report="estado-cuenta-vacio"', false)
            ->assertSee('Selecciona un cliente para cargar su estado de cuenta.')
            ->assertDontSee('<table class="table"', false);
    }

    public function test_reportes_respetan_urbanizacion_seleccionada(): void
    {
        [$admin, $urbanizacion] = $this->adminConUrbanizacion();
        $otraUrbanizacion = Urbanizacion::whereKeyNot($urbanizacion->id)->firstOrFail();
        $manzano = Manzano::where('urbanizacion_id', $otraUrbanizacion->id)->firstOrFail();

        Lote::create([
            'manzano_id' => $manzano->id,
            'codigo' => 'SOLO-OTRA',
            'superficie' => 321,
            'precio' => 99999,
            'estado' => 'disponible',
        ]);

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->get(route('reportes.lotes-estado'))
            ->assertOk()
            ->assertDontSee('SOLO-OTRA');
    }

    private function adminConUrbanizacion(): array
    {
        $this->seed();

        return [
            User::where('email', 'admin@impacto.test')->firstOrFail(),
            Urbanizacion::firstOrFail(),
        ];
    }
}
