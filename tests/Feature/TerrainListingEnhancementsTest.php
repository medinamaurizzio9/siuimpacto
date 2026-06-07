<?php

namespace Tests\Feature;

use App\Models\Lote;
use App\Models\Manzano;
use App\Models\Urbanizacion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TerrainListingEnhancementsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Urbanizacion $urbanizacion;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $this->admin = User::where('email', 'admin@impacto.test')->firstOrFail();
        $this->urbanizacion = Urbanizacion::orderBy('id')->firstOrFail();
    }

    public function test_seleccion_de_urbanizacion_usa_grid_de_cuatro_columnas(): void
    {
        $this->actingAs($this->admin)
            ->get(route('urbanizaciones.select'))
            ->assertOk()
            ->assertSee('grid-urbanizaciones', false);

        $css = file_get_contents(public_path('css/app.css'));

        $this->assertStringContainsString('.grid-urbanizaciones { grid-template-columns: repeat(4, minmax(0, 1fr));', $css);
        $this->assertStringContainsString('height: 170px', $css);
    }

    public function test_lotes_filtra_por_urbanizacion_estado_manzano_y_codigo(): void
    {
        $manzanoActual = $this->urbanizacion->manzanos()->firstOrFail();
        $otraUrbanizacion = Urbanizacion::whereKeyNot($this->urbanizacion->id)->firstOrFail();
        $otroManzano = $otraUrbanizacion->manzanos()->firstOrFail();

        Lote::create(['manzano_id' => $manzanoActual->id, 'codigo' => 'CTX-ACTUAL', 'superficie' => 350, 'precio' => 25000, 'estado' => 'reservado']);
        Lote::create(['manzano_id' => $otroManzano->id, 'codigo' => 'CTX-OTRA', 'superficie' => 350, 'precio' => 25000, 'estado' => 'reservado']);

        $this->actingAs($this->admin)
            ->withSession(['urbanizacion_id' => $this->urbanizacion->id])
            ->get(route('lotes.index', ['buscar' => 'CTX-', 'estado' => 'reservado', 'manzano_id' => $manzanoActual->id]))
            ->assertOk()
            ->assertSee('Urbanización: '.$this->urbanizacion->nombre.' / Lotes')
            ->assertSee('CTX-ACTUAL')
            ->assertDontSee('CTX-OTRA')
            ->assertSee('Buscar lote/código')
            ->assertSee('Superficie desde')
            ->assertDontSee('<th>Urbanizacion</th>', false);
    }

    public function test_lotes_pagina_quince_y_conserva_filtros(): void
    {
        $manzano = $this->urbanizacion->manzanos()->firstOrFail();

        foreach (range(1, 17) as $numero) {
            Lote::create([
                'manzano_id' => $manzano->id,
                'codigo' => 'PAG-'.str_pad((string) $numero, 2, '0', STR_PAD_LEFT),
                'superficie' => 300,
                'precio' => 20000,
                'estado' => 'disponible',
            ]);
        }

        $response = $this->actingAs($this->admin)
            ->withSession(['urbanizacion_id' => $this->urbanizacion->id])
            ->get(route('lotes.index', ['buscar' => 'PAG-']));

        $response->assertOk()->assertViewHas('lotes', function ($lotes) {
            return $lotes->perPage() === 15 && $lotes->total() === 17;
        });
        $response->assertSee('buscar=PAG-', false);
    }

    public function test_manzanos_filtra_por_urbanizacion_y_pagina_quince(): void
    {
        $otraUrbanizacion = Urbanizacion::whereKeyNot($this->urbanizacion->id)->firstOrFail();
        Manzano::create(['urbanizacion_id' => $otraUrbanizacion->id, 'codigo' => 'PAG-M-OTRO', 'nombre' => 'Fuera de contexto']);

        foreach (range(1, 17) as $numero) {
            Manzano::create([
                'urbanizacion_id' => $this->urbanizacion->id,
                'codigo' => 'PAG-M-'.str_pad((string) $numero, 2, '0', STR_PAD_LEFT),
                'nombre' => 'Manzano paginado '.$numero,
            ]);
        }

        $response = $this->actingAs($this->admin)
            ->withSession(['urbanizacion_id' => $this->urbanizacion->id])
            ->get(route('manzanos.index', ['buscar' => 'PAG-M-']));

        $response->assertOk()
            ->assertSee('Urbanización: '.$this->urbanizacion->nombre.' / Manzanos')
            ->assertSee('Buscar por código o nombre')
            ->assertDontSee('PAG-M-OTRO')
            ->assertDontSee('<th>Urbanizacion</th>', false)
            ->assertViewHas('manzanos', fn ($manzanos) => $manzanos->perPage() === 15 && $manzanos->total() === 17)
            ->assertSee('buscar=PAG-M-', false);
    }

    public function test_urbanizacion_guarda_y_muestra_propietario(): void
    {
        $this->actingAs($this->admin)
            ->withSession(['urbanizacion_id' => $this->urbanizacion->id])
            ->post(route('urbanizaciones.store'), [
                'nombre' => 'Proyecto Propietario',
                'propietario' => 'Inversiones del Valle SRL',
                'ubicacion' => 'Santa Cruz',
                'superficie_total' => 12000,
                'estado' => 'activa',
                'mostrar_precio_publico' => 1,
            ])
            ->assertRedirect(route('urbanizaciones.index'));

        $this->assertDatabaseHas('urbanizaciones', [
            'nombre' => 'Proyecto Propietario',
            'propietario' => 'Inversiones del Valle SRL',
        ]);

        $this->actingAs($this->admin)
            ->withSession(['urbanizacion_id' => $this->urbanizacion->id])
            ->get(route('urbanizaciones.index'))
            ->assertOk()
            ->assertSee('Propietario')
            ->assertSee('Inversiones del Valle SRL');

        $this->actingAs($this->admin)
            ->get(route('urbanizaciones.select'))
            ->assertOk()
            ->assertSee('Propietario:')
            ->assertSee('Inversiones del Valle SRL');
    }
}
