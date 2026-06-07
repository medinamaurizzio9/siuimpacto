<?php

namespace Tests\Feature;

use App\Models\Asesor;
use App\Models\Cliente;
use App\Models\Lote;
use App\Models\Reserva;
use App\Models\Urbanizacion;
use App\Models\User;
use App\Services\ReservationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservaReciboPermisosTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $supervisor;

    private User $vendedor;

    private Urbanizacion $urbanizacion;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $this->admin = User::where('email', 'admin@impacto.test')->firstOrFail();
        $this->supervisor = User::where('email', 'supervisor@impacto.test')->firstOrFail();
        $this->vendedor = User::where('email', 'vendedor@impacto.test')->firstOrFail();
        $this->urbanizacion = $this->vendedor->urbanizacionesAsignadas()->firstOrFail();
    }

    public function test_vendedor_puede_abrir_recibo_de_su_reserva_y_ve_boton(): void
    {
        $reserva = $this->createReservationFor($this->vendedor);

        $this->actingAs($this->vendedor)
            ->withSession(['urbanizacion_id' => $this->urbanizacion->id])
            ->get(route('reservas.recibo', $reserva))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $this->actingAs($this->vendedor)
            ->withSession(['urbanizacion_id' => $this->urbanizacion->id])
            ->get(route('reservas.index'))
            ->assertOk()
            ->assertSee('href="'.route('reservas.recibo', $reserva).'" target="_blank"', false)
            ->assertSee('Recibo PDF');
    }

    public function test_vendedor_no_puede_abrir_recibo_de_otro_vendedor_ni_caja(): void
    {
        $otroVendedor = $this->createSeller($this->supervisor, 'otro-vendedor@test.local');
        $reserva = $this->createReservationFor($otroVendedor);

        $this->actingAs($this->vendedor)
            ->withSession(['urbanizacion_id' => $this->urbanizacion->id])
            ->get(route('reservas.recibo', $reserva))
            ->assertForbidden();

        $this->actingAs($this->vendedor)
            ->withSession(['urbanizacion_id' => $this->urbanizacion->id])
            ->get(route('caja.index'))
            ->assertForbidden();

        $movimiento = $reserva->cashMovements()->firstOrFail();
        $this->actingAs($this->vendedor)
            ->withSession(['urbanizacion_id' => $this->urbanizacion->id])
            ->get(route('pdf.recibo', $movimiento))
            ->assertForbidden();

        $this->actingAs($this->vendedor)
            ->withSession(['urbanizacion_id' => $this->urbanizacion->id])
            ->get(route('reservas.index'))
            ->assertOk()
            ->assertDontSee(route('caja.index'), false);
    }

    public function test_supervisor_puede_abrir_recibo_de_asesor_de_su_equipo(): void
    {
        $reserva = $this->createReservationFor($this->vendedor);

        $this->actingAs($this->supervisor)
            ->withSession(['urbanizacion_id' => $this->urbanizacion->id])
            ->get(route('reservas.recibo', $reserva))
            ->assertOk();
    }

    public function test_supervisor_no_puede_abrir_recibo_de_otro_equipo(): void
    {
        $otroSupervisor = User::factory()->create(['name' => 'Supervisor Ajeno', 'email' => 'supervisor-ajeno@test.local']);
        $otroSupervisor->assignRole('supervisor');
        $otroSupervisor->urbanizacionesAsignadas()->syncWithoutDetaching([$this->urbanizacion->id => ['activo' => true]]);
        $otroVendedor = $this->createSeller($otroSupervisor, 'asesor-ajeno@test.local');
        $reserva = $this->createReservationFor($otroVendedor);

        $this->actingAs($this->supervisor)
            ->withSession(['urbanizacion_id' => $this->urbanizacion->id])
            ->get(route('reservas.recibo', $reserva))
            ->assertForbidden();
    }

    public function test_admin_puede_abrir_cualquier_recibo_de_reserva(): void
    {
        $reserva = $this->createReservationFor($this->vendedor);

        $this->actingAs($this->admin)
            ->withSession(['urbanizacion_id' => $this->urbanizacion->id])
            ->get(route('reservas.recibo', $reserva))
            ->assertOk();
    }

    private function createSeller(User $supervisor, string $email): User
    {
        $seller = User::factory()->create(['name' => 'Asesor Prueba', 'email' => $email]);
        $seller->assignRole('vendedor');
        $seller->urbanizacionesAsignadas()->syncWithoutDetaching([$this->urbanizacion->id => ['activo' => true]]);
        Asesor::create([
            'user_id' => $seller->id,
            'supervisor_id' => $supervisor->id,
            'nombre' => 'Asesor',
            'apellido' => 'Prueba',
            'ci' => 'CI-'.$seller->id,
            'email' => $email,
            'activo' => true,
        ]);

        return $seller;
    }

    private function createReservationFor(User $user): Reserva
    {
        $lote = Lote::whereHas('manzano', fn ($query) => $query->where('urbanizacion_id', $this->urbanizacion->id))
            ->where('estado', 'disponible')
            ->firstOrFail();
        $cliente = Cliente::create([
            'urbanizacion_id' => $this->urbanizacion->id,
            'created_by' => $user->id,
            'nombre' => 'Cliente Recibo '.$user->id.' '.$lote->id,
            'documento' => 'DOC-'.$user->id.'-'.$lote->id,
        ]);

        return app(ReservationService::class)->create([
            'cliente_id' => $cliente->id,
            'lote_id' => $lote->id,
            'fecha_reserva' => now()->toDateString(),
            'fecha_vencimiento' => now()->addDays(5)->toDateString(),
            'monto_reserva' => 500,
            'tipo_operacion' => 'contado',
            'metodo_pago' => 'efectivo',
            'referencia' => 'RES-'.$user->id.'-'.$lote->id,
        ], $user);
    }
}
