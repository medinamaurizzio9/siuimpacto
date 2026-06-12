<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\CommercialSetting;
use App\Models\Lote;
use App\Models\Urbanizacion;
use App\Models\User;
use App\Services\LotPricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoteCommercialUpdateTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Urbanizacion $urbanizacion;
    private Cliente $cliente;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $this->admin = User::where('email', 'admin@impacto.test')->firstOrFail();
        $this->urbanizacion = Urbanizacion::firstOrFail();
        $this->cliente = Cliente::where('urbanizacion_id', $this->urbanizacion->id)->firstOrFail();

        CommercialSetting::updateOrCreate(['key' => 'tipo_cambio_usd_bs'], ['value' => '6.96']);
        CommercialSetting::updateOrCreate(['key' => 'incremento_credito_tipo'], ['value' => 'monto']);
        CommercialSetting::updateOrCreate(['key' => 'incremento_credito_valor'], ['value' => '3000']);
    }

    public function test_administrador_ve_controles_y_vendedor_no_los_ve(): void
    {
        $this->actingAs($this->admin)
            ->withSession(['urbanizacion_id' => $this->urbanizacion->id])
            ->get(route('lotes.index'))
            ->assertOk()
            ->assertSee('Editar rapido')
            ->assertSee('Actualizacion Masiva')
            ->assertSee('inline-money-input');

        $vendedor = User::where('email', 'vendedor@impacto.test')->firstOrFail();
        $urbanizacion = $vendedor->urbanizacionesAsignadas()->firstOrFail();

        $this->actingAs($vendedor)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->get(route('lotes.index'))
            ->assertOk()
            ->assertDontSee('Editar rapido')
            ->assertDontSee('Actualizacion Masiva')
            ->assertDontSee('inline-money-input');
    }

    public function test_administrador_actualiza_precio_real_y_cuota_inicial_individual(): void
    {
        $lote = $this->loteDisponible();

        $this->actingAs($this->admin)
            ->withSession(['urbanizacion_id' => $this->urbanizacion->id])
            ->patch(route('lotes.comercial-rapido', $lote), [
                'precio_real_usd' => 25000,
                'cuota_inicial_valor' => 5000,
            ])
            ->assertRedirect()
            ->assertSessionHas('status', 'Datos comerciales del lote actualizados correctamente.');

        $this->assertDatabaseHas('lotes', [
            'id' => $lote->id,
            'precio_real_override_usd' => 25000,
            'cuota_inicial_tipo' => 'monto',
            'cuota_inicial_valor' => 5000,
        ]);
        $this->assertDatabaseHas('audit_logs', ['modelo' => 'Lote', 'modelo_id' => $lote->id, 'accion' => 'edicion_rapida_precio_real']);
        $this->assertDatabaseHas('audit_logs', ['modelo' => 'Lote', 'modelo_id' => $lote->id, 'accion' => 'edicion_rapida_cuota_inicial']);
    }

    public function test_usuarios_no_autorizados_reciben_403_en_actualizaciones_comerciales(): void
    {
        $lote = $this->loteDisponible();

        foreach (['gerente@impacto.test', 'vendedor@impacto.test'] as $email) {
            $user = User::where('email', $email)->firstOrFail();
            $urbanizacionId = $user->hasRole('vendedor')
                ? $user->urbanizacionesAsignadas()->firstOrFail()->id
                : $this->urbanizacion->id;

            $this->actingAs($user)
                ->withSession(['urbanizacion_id' => $urbanizacionId])
                ->patch(route('lotes.comercial-rapido', $lote), [
                    'precio_real_usd' => 25000,
                    'cuota_inicial_valor' => 5000,
                ])
                ->assertForbidden();

            $this->actingAs($user)
                ->withSession(['urbanizacion_id' => $urbanizacionId])
                ->post(route('lotes.comercial-masivo'), [
                    'scope' => 'todos',
                    'operation' => 'reemplazar_cuota',
                    'valor' => 5000,
                ])
                ->assertForbidden();
        }
    }

    public function test_administrador_hace_actualizacion_masiva_de_cuota_y_precio_real(): void
    {
        $loteA = $this->loteDisponible();
        $loteB = $this->loteDisponible(exceptId: $loteA->id);

        $this->actingAs($this->admin)
            ->withSession(['urbanizacion_id' => $this->urbanizacion->id])
            ->post(route('lotes.comercial-masivo'), [
                'scope' => 'manzano',
                'manzano_id' => $loteA->manzano_id,
                'operation' => 'reemplazar_cuota',
                'valor' => 4000,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('lotes', ['id' => $loteA->id, 'cuota_inicial_tipo' => 'monto', 'cuota_inicial_valor' => 4000]);

        $this->actingAs($this->admin)
            ->withSession(['urbanizacion_id' => $this->urbanizacion->id])
            ->post(route('lotes.comercial-masivo'), [
                'scope' => 'filtrados',
                'buscar' => $loteB->codigo,
                'operation' => 'reemplazar_precio_real',
                'valor' => 28000,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('lotes', ['id' => $loteB->id, 'precio_real_override_usd' => 28000]);
        $this->assertDatabaseHas('audit_logs', ['modelo' => 'Lote', 'modelo_id' => $loteB->id, 'accion' => 'actualizacion_masiva_precio_real']);
    }

    public function test_override_se_usa_en_mapa_y_disponibilidad(): void
    {
        $this->urbanizacion->update(['plano_imagen' => 'planos/demo.jpg']);
        $lote = $this->loteDisponible();
        $lote->update(['coord_x' => 35, 'coord_y' => 40, 'precio_real_override_usd' => 29000]);

        $this->actingAs($this->admin)
            ->withSession(['urbanizacion_id' => $this->urbanizacion->id])
            ->get(route('mapa'))
            ->assertOk()
            ->assertSee('data-precio="$us 29,000.00"', false);

        $this->get(route('disponibilidad.publica', ['urbanizacion_id' => $this->urbanizacion->id]))
            ->assertOk()
            ->assertSee('$us 29,000.00');
    }

    public function test_credito_usa_override_y_contado_semicontado_usan_precio_oportunidad(): void
    {
        $credito = $this->loteDisponible();
        $credito->update(['precio' => 20000, 'precio_real_override_usd' => 26000, 'cuota_inicial_tipo' => 'monto', 'cuota_inicial_valor' => 3000]);

        $this->crearVenta($credito, 'credito', 12);
        $this->assertDatabaseHas('ventas', [
            'lote_id' => $credito->id,
            'tipo_operacion' => 'credito',
            'precio_final' => 26000,
            'incremento_credito_aplicado' => 6000,
        ]);

        $contado = $this->loteDisponible(exceptId: $credito->id);
        $contado->update(['precio' => 21000, 'precio_real_override_usd' => 27000]);
        $this->crearVenta($contado, 'contado', 0);
        $this->assertDatabaseHas('ventas', ['lote_id' => $contado->id, 'tipo_operacion' => 'contado', 'precio_final' => 21000]);

        $semicontado = $this->loteDisponible(exceptId: $contado->id);
        $semicontado->update(['precio' => 22000, 'precio_real_override_usd' => 28000]);
        $this->crearVenta($semicontado, 'semicontado', 1);
        $this->assertDatabaseHas('ventas', ['lote_id' => $semicontado->id, 'tipo_operacion' => 'semicontado', 'precio_final' => 22000]);
    }

    public function test_limpiar_override_vuelve_al_calculo_automatico(): void
    {
        $lote = $this->loteDisponible();
        $lote->update(['precio' => 20000, 'precio_real_override_usd' => 26000]);

        $this->actingAs($this->admin)
            ->withSession(['urbanizacion_id' => $this->urbanizacion->id])
            ->post(route('lotes.comercial-masivo'), [
                'scope' => 'filtrados',
                'buscar' => $lote->codigo,
                'operation' => 'limpiar_precio_real',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('lotes', ['id' => $lote->id, 'precio_real_override_usd' => null]);
        $this->assertSame(23000.0, app(LotPricingService::class)->creditUsd($lote->fresh()));

        $this->actingAs($this->admin)
            ->withSession(['urbanizacion_id' => $this->urbanizacion->id])
            ->get(route('lotes.index', ['buscar' => $lote->codigo]))
            ->assertOk()
            ->assertSee('value="23000.00"', false);
    }

    private function loteDisponible(?int $exceptId = null): Lote
    {
        return Lote::whereHas('manzano', fn ($query) => $query->where('urbanizacion_id', $this->urbanizacion->id))
            ->where('estado', 'disponible')
            ->when($exceptId, fn ($query) => $query->whereKeyNot($exceptId))
            ->firstOrFail();
    }

    private function crearVenta(Lote $lote, string $tipoOperacion, int $numeroCuotas): void
    {
        $this->actingAs($this->admin)
            ->withSession(['urbanizacion_id' => $this->urbanizacion->id])
            ->post(route('ventas.store'), [
                'cliente_id' => $this->cliente->id,
                'lote_id' => $lote->id,
                'tipo_operacion' => $tipoOperacion,
                'fecha_venta' => now()->toDateString(),
                'precio_final' => 1,
                'cuota_inicial' => 1,
                'numero_cuotas' => $numeroCuotas,
                'estado' => 'activa',
                'metodo_pago' => 'efectivo',
            ])
            ->assertRedirect(route('ventas.index'));
    }
}
