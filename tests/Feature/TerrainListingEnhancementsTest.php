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

    public function test_lotes_pagina_cincuenta_y_conserva_filtros(): void
    {
        $manzano = $this->urbanizacion->manzanos()->firstOrFail();

        foreach (range(1, 57) as $numero) {
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
            return $lotes->perPage() === 50 && $lotes->total() === 57;
        });
        $response->assertSee('pagination-wrapper', false)
            ->assertSee('buscar=PAG-', false)
            ->assertSee('page=2', false);
    }

    public function test_lote_guarda_muestra_y_expone_cuota_inicial_configurada(): void
    {
        $manzano = $this->urbanizacion->manzanos()->firstOrFail();

        $this->actingAs($this->admin)
            ->withSession(['urbanizacion_id' => $this->urbanizacion->id])
            ->post(route('lotes.store'), [
                'manzano_id' => $manzano->id,
                'codigo' => 'CI-LOTE',
                'superficie' => 320,
                'precio' => 30000,
                'cuota_inicial_tipo' => 'porcentaje',
                'cuota_inicial_valor' => 20,
                'estado' => 'disponible',
                'fila' => 1,
                'columna' => 1,
                'coord_x' => null,
                'coord_y' => null,
                'observaciones' => null,
            ])
            ->assertRedirect(route('lotes.index'));

        $lote = Lote::where('codigo', 'CI-LOTE')->firstOrFail();

        $this->assertDatabaseHas('lotes', [
            'id' => $lote->id,
            'cuota_inicial_tipo' => 'porcentaje',
            'cuota_inicial_valor' => 20,
        ]);

        $this->actingAs($this->admin)
            ->withSession(['urbanizacion_id' => $this->urbanizacion->id])
            ->get(route('lotes.index', ['buscar' => 'CI-LOTE']))
            ->assertOk()
            ->assertSee('Cuota inicial')
            ->assertSee('20%');

        $this->actingAs($this->admin)
            ->withSession(['urbanizacion_id' => $this->urbanizacion->id])
            ->get(route('lotes.show', $lote))
            ->assertOk()
            ->assertSee('Cuota inicial')
            ->assertSee('20%');

        $this->actingAs($this->admin)
            ->withSession(['urbanizacion_id' => $this->urbanizacion->id])
            ->get(route('ventas.create', ['lote_id' => $lote->id]))
            ->assertOk()
            ->assertSee('Cuota inicial configurada del lote')
            ->assertSee('20%');
    }

    public function test_lote_rechaza_porcentaje_de_cuota_inicial_mayor_a_cien(): void
    {
        $manzano = $this->urbanizacion->manzanos()->firstOrFail();

        $this->actingAs($this->admin)
            ->withSession(['urbanizacion_id' => $this->urbanizacion->id])
            ->post(route('lotes.store'), [
                'manzano_id' => $manzano->id,
                'codigo' => 'CI-INVALIDO',
                'superficie' => 320,
                'precio' => 30000,
                'cuota_inicial_tipo' => 'porcentaje',
                'cuota_inicial_valor' => 120,
                'estado' => 'disponible',
                'fila' => 1,
                'columna' => 1,
                'coord_x' => null,
                'coord_y' => null,
            ])
            ->assertSessionHasErrors('cuota_inicial_valor');
    }

    public function test_editar_cuota_inicial_registra_historial_de_lote(): void
    {
        $lote = Lote::whereHas('manzano', fn ($query) => $query->where('urbanizacion_id', $this->urbanizacion->id))->firstOrFail();

        $this->actingAs($this->admin)
            ->withSession(['urbanizacion_id' => $this->urbanizacion->id])
            ->put(route('lotes.update', $lote), [
                'manzano_id' => $lote->manzano_id,
                'codigo' => $lote->codigo,
                'superficie' => $lote->superficie,
                'precio' => $lote->precio,
                'cuota_inicial_tipo' => 'monto',
                'cuota_inicial_valor' => 5000,
                'estado' => $lote->estado,
                'fila' => $lote->fila,
                'columna' => $lote->columna,
                'coord_x' => $lote->coord_x,
                'coord_y' => $lote->coord_y,
                'observaciones' => $lote->observaciones,
            ])
            ->assertRedirect(route('lotes.index'));

        $this->assertDatabaseHas('lot_histories', [
            'lote_id' => $lote->id,
            'accion' => 'cambio_cuota_inicial',
        ]);
    }

    public function test_manzanos_filtra_por_urbanizacion_y_pagina_cincuenta(): void
    {
        $otraUrbanizacion = Urbanizacion::whereKeyNot($this->urbanizacion->id)->firstOrFail();
        Manzano::create(['urbanizacion_id' => $otraUrbanizacion->id, 'codigo' => 'PAG-M-OTRO', 'nombre' => 'Fuera de contexto']);

        foreach (range(1, 57) as $numero) {
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
            ->assertViewHas('manzanos', fn ($manzanos) => $manzanos->perPage() === 50 && $manzanos->total() === 57)
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
