<?php

namespace Tests\Feature;

use App\Models\Urbanizacion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UrbanizacionListadoTest extends TestCase
{
    use RefreshDatabase;

    public function test_listado_muestra_cantidad_real_de_manzanos_y_terrenos(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@impacto.test')->firstOrFail();
        $urbanizacion = Urbanizacion::withCount(['manzanos', 'lotes'])->firstOrFail();

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->get(route('urbanizaciones.index'))
            ->assertOk()
            ->assertSee('Terrenos')
            ->assertSee((string) $urbanizacion->manzanos_count)
            ->assertSee((string) $urbanizacion->lotes_count)
            ->assertViewHas('urbanizaciones', function ($urbanizaciones) use ($urbanizacion) {
                $listed = $urbanizaciones->firstWhere('id', $urbanizacion->id);

                return $listed
                    && (int) $listed->total_lotes === $urbanizacion->lotes_count
                    && (int) $listed->manzanos_count === $urbanizacion->manzanos_count;
            });
    }

    public function test_tarjeta_de_seleccion_muestra_todos_los_conteos_de_terrenos(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@impacto.test')->firstOrFail();
        $urbanizacion = Urbanizacion::firstOrFail();
        $counts = [
            'total' => $urbanizacion->lotes()->count(),
            'disponibles' => $urbanizacion->lotes()->where('estado', 'disponible')->count(),
            'vendidos' => $urbanizacion->lotes()->where('estado', 'vendido')->count(),
            'reservados' => $urbanizacion->lotes()->where('estado', 'reservado')->count(),
            'bloqueados' => $urbanizacion->lotes()->where('estado', 'bloqueado')->count(),
        ];

        $this->actingAs($admin)
            ->get(route('urbanizaciones.select'))
            ->assertOk()
            ->assertSee('Total terrenos:')
            ->assertSee('Disponibles:')
            ->assertSee('Vendidos:')
            ->assertSee('Reservados:')
            ->assertSee('Bloqueados:')
            ->assertViewHas('urbanizaciones', function ($urbanizaciones) use ($urbanizacion, $counts) {
                $listed = $urbanizaciones->firstWhere('id', $urbanizacion->id);

                return $listed
                    && (int) $listed->total_lotes === $counts['total']
                    && (int) $listed->disponibles_count === $counts['disponibles']
                    && (int) $listed->vendidos_count === $counts['vendidos']
                    && (int) $listed->reservados_count === $counts['reservados']
                    && (int) $listed->bloqueados_count === $counts['bloqueados'];
            });
    }
}
