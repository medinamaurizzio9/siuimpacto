<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Lote;
use App\Models\Reserva;
use App\Models\Urbanizacion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservaClienteAutocompleteTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Urbanizacion $urbanizacion;

    private Cliente $cliente;

    private Lote $lote;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $this->admin = User::where('email', 'admin@impacto.test')->firstOrFail();
        $this->urbanizacion = Urbanizacion::firstOrFail();
        $this->cliente = Cliente::where('urbanizacion_id', $this->urbanizacion->id)->firstOrFail();
        $this->lote = Lote::whereHas('manzano', fn ($query) => $query->where('urbanizacion_id', $this->urbanizacion->id))
            ->where('estado', 'disponible')
            ->firstOrFail();
    }

    public function test_busqueda_devuelve_json_limpio_con_campos_esperados(): void
    {
        $response = $this->actingAs($this->admin)
            ->withSession(['urbanizacion_id' => $this->urbanizacion->id])
            ->getJson(route('clientes.buscar', ['q' => $this->cliente->documento]));

        $response->assertOk()->assertJsonCount(1);
        $this->assertSame(
            ['id', 'nombre', 'documento', 'telefono', 'email'],
            array_keys($response->json()[0])
        );
    }

    public function test_crear_reserva_falla_si_cliente_id_no_es_valido(): void
    {
        $reservationCount = Reserva::count();

        $this->actingAs($this->admin)
            ->withSession(['urbanizacion_id' => $this->urbanizacion->id])
            ->post(route('reservas.store'), $this->reservationData(['cliente_id' => 999999]))
            ->assertSessionHasErrors(['cliente_id' => 'Debe seleccionar un cliente válido de la lista.']);

        $this->assertDatabaseCount('reservas', $reservationCount);
    }

    public function test_crear_reserva_funciona_con_cliente_id_valido(): void
    {
        $this->actingAs($this->admin)
            ->withSession(['urbanizacion_id' => $this->urbanizacion->id])
            ->post(route('reservas.store'), $this->reservationData())
            ->assertRedirect(route('reservas.index'));

        $this->assertDatabaseHas('reservas', [
            'cliente_id' => $this->cliente->id,
            'lote_id' => $this->lote->id,
            'estado' => 'activa',
        ]);
    }

    public function test_formulario_mantiene_lote_y_renderiza_un_solo_cliente_preseleccionado(): void
    {
        $response = $this->actingAs($this->admin)
            ->withSession(['urbanizacion_id' => $this->urbanizacion->id])
            ->get(route('reservas.create', [
                'lote_id' => $this->lote->id,
                'cliente_id' => $this->cliente->id,
            ]));

        $response->assertOk()
            ->assertSee('value="'.$this->lote->id.'" selected', false)
            ->assertSee('id="cliente_id" name="cliente_id" data-cliente-id value="'.$this->cliente->id.'"', false)
            ->assertSee($this->cliente->nombre)
            ->assertSee($this->cliente->documento);

        $this->assertSame(1, substr_count($response->getContent(), 'id="cliente_selected_card"'));
        $this->assertSame(1, substr_count($response->getContent(), 'id="cliente_selected_nombre"'));
        $this->assertSame(1, substr_count($response->getContent(), 'id="cliente_results"'));
        $response->assertSee('id="cliente_selected_card" class="cliente-selected-card" data-cliente-card style="display:block"', false)
            ->assertSee('id="cliente_results" class="cliente-search-results" data-cliente-results hidden style="display:none"', false);
    }

    private function reservationData(array $overrides = []): array
    {
        return [
            'cliente_id' => $this->cliente->id,
            'lote_id' => $this->lote->id,
            'fecha_reserva' => now()->toDateString(),
            'fecha_vencimiento' => now()->addDays(7)->toDateString(),
            'monto_reserva' => 100,
            'tipo_operacion' => 'contado',
            'metodo_pago' => 'efectivo',
            ...$overrides,
        ];
    }
}
