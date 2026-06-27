<?php

namespace Tests\Feature;

use App\Models\Asesor;
use App\Models\Cliente;
use App\Models\Cuota;
use App\Models\GrupoComercial;
use App\Models\Lote;
use App\Models\Reserva;
use App\Models\SupervisorProfile;
use App\Models\Urbanizacion;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeletionDependencyMessagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_cliente_con_ventas_no_se_elimina_y_muestra_ventas_y_cuotas(): void
    {
        [$admin, $urbanizacion] = $this->adminContext();
        $cliente = $this->cliente($urbanizacion, 'Cliente Venta', 'CLI-VENTA');
        $venta = $this->venta($cliente);
        Cuota::create([
            'venta_id' => $venta->id,
            'numero' => 1,
            'fecha_programada' => now()->addMonth(),
            'fecha_vencimiento' => now()->addMonth(),
            'monto' => 100,
            'monto_pagado' => 0,
            'saldo_pendiente' => 100,
            'estado' => 'pendiente',
        ]);

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->delete(route('clientes.destroy', $cliente))
            ->assertRedirect()
            ->assertSessionHasErrors('delete');

        $message = session('errors')->first('delete');
        $this->assertStringContainsString('ventas registradas', $message);
        $this->assertStringContainsString('cuotas relacionadas', $message);
        $this->assertDatabaseHas('clientes', ['id' => $cliente->id]);
    }

    public function test_cliente_con_reserva_no_se_elimina_y_muestra_reservas(): void
    {
        [$admin, $urbanizacion] = $this->adminContext();
        $cliente = $this->cliente($urbanizacion, 'Cliente Reserva', 'CLI-RES');
        Reserva::create([
            'cliente_id' => $cliente->id,
            'lote_id' => Lote::firstOrFail()->id,
            'usuario_id' => $admin->id,
            'fecha_reserva' => now(),
            'fecha_vencimiento' => now()->addDays(5),
            'monto_reserva' => 100,
            'estado' => 'activa',
            'tipo_operacion' => 'contado',
        ]);

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->delete(route('clientes.destroy', $cliente))
            ->assertRedirect()
            ->assertSessionHasErrors('delete');

        $this->assertStringContainsString('reservas activas', session('errors')->first('delete'));
        $this->assertDatabaseHas('clientes', ['id' => $cliente->id]);
    }

    public function test_asesor_con_clientes_no_se_elimina_y_muestra_clientes(): void
    {
        [$admin, $urbanizacion] = $this->adminContext();
        $asesor = $this->asesor($urbanizacion, 'asesor.clientes@test.local', 'ASE-CLI');
        $this->cliente($urbanizacion, 'Cliente Asesor', 'CLI-ASE', $asesor->user_id);

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->delete(route('asesores.destroy', $asesor))
            ->assertRedirect()
            ->assertSessionHasErrors('delete');

        $this->assertStringContainsString('clientes registrados', session('errors')->first('delete'));
        $this->assertDatabaseHas('asesores', ['id' => $asesor->id]);
    }

    public function test_asesor_con_reservas_no_se_elimina_y_muestra_reservas(): void
    {
        [$admin, $urbanizacion] = $this->adminContext();
        $asesor = $this->asesor($urbanizacion, 'asesor.reservas@test.local', 'ASE-RES');
        $cliente = $this->cliente($urbanizacion, 'Cliente Reserva Asesor', 'CLI-ASE-RES', $asesor->user_id);
        Reserva::create([
            'cliente_id' => $cliente->id,
            'lote_id' => Lote::firstOrFail()->id,
            'usuario_id' => $asesor->user_id,
            'fecha_reserva' => now(),
            'fecha_vencimiento' => now()->addDays(5),
            'monto_reserva' => 100,
            'estado' => 'activa',
            'tipo_operacion' => 'contado',
        ]);

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->delete(route('asesores.destroy', $asesor))
            ->assertRedirect()
            ->assertSessionHasErrors('delete');

        $message = session('errors')->first('delete');
        $this->assertStringContainsString('reservas creadas', $message);
        $this->assertDatabaseHas('asesores', ['id' => $asesor->id]);
    }

    public function test_supervisor_con_asesores_no_se_elimina_y_muestra_asesores(): void
    {
        [$admin, $urbanizacion] = $this->adminContext();
        $supervisor = $this->supervisor('supervisor.asesores@test.local', 'SUP-ASE');
        $this->asesor($urbanizacion, 'asesor.equipo@test.local', 'ASE-EQUIPO', $supervisor->user_id);

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->delete(route('supervisores.destroy', $supervisor))
            ->assertRedirect()
            ->assertSessionHasErrors('delete');

        $this->assertStringContainsString('asesores asignados', session('errors')->first('delete'));
        $this->assertDatabaseHas('supervisor_profiles', ['id' => $supervisor->id]);
    }

    public function test_supervisor_con_grupo_comercial_no_se_elimina_y_muestra_grupo(): void
    {
        [$admin, $urbanizacion] = $this->adminContext();
        $supervisor = $this->supervisor('supervisor.grupo@test.local', 'SUP-GRP');
        GrupoComercial::create([
            'nombre' => 'Equipo Norte',
            'supervisor_id' => $supervisor->user_id,
            'activo' => true,
        ]);

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->delete(route('supervisores.destroy', $supervisor))
            ->assertRedirect()
            ->assertSessionHasErrors('delete');

        $this->assertStringContainsString('grupos comerciales bajo su responsabilidad', session('errors')->first('delete'));
        $this->assertDatabaseHas('supervisor_profiles', ['id' => $supervisor->id]);
    }

    public function test_cliente_sin_relaciones_se_elimina_correctamente(): void
    {
        [$admin, $urbanizacion] = $this->adminContext();
        $cliente = $this->cliente($urbanizacion, 'Cliente Libre', 'CLI-LIBRE');

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->delete(route('clientes.destroy', $cliente))
            ->assertRedirect()
            ->assertSessionHas('status', 'Cliente eliminado.');

        $this->assertDatabaseMissing('clientes', ['id' => $cliente->id]);
    }

    private function adminContext(): array
    {
        $this->seed();

        return [
            User::where('email', 'admin@impacto.test')->firstOrFail(),
            Urbanizacion::firstOrFail(),
        ];
    }

    private function cliente(Urbanizacion $urbanizacion, string $nombre, string $documento, ?int $createdBy = null): Cliente
    {
        return Cliente::create([
            'urbanizacion_id' => $urbanizacion->id,
            'created_by' => $createdBy,
            'nombre' => $nombre,
            'documento' => $documento,
            'telefono' => '70000000',
            'email' => strtolower($documento).'@test.local',
        ]);
    }

    private function venta(Cliente $cliente): Venta
    {
        return Venta::create([
            'lote_id' => Lote::firstOrFail()->id,
            'cliente_id' => $cliente->id,
            'user_id' => User::where('email', 'admin@impacto.test')->firstOrFail()->id,
            'fecha_venta' => now()->toDateString(),
            'precio_final' => 10000,
            'cuota_inicial' => 1000,
            'numero_cuotas' => 1,
            'estado' => 'activa',
        ]);
    }

    private function asesor(Urbanizacion $urbanizacion, string $email, string $ci, ?int $supervisorId = null): Asesor
    {
        $user = User::factory()->create(['name' => 'Usuario '.$ci, 'email' => $email]);
        $user->assignRole('vendedor');
        $user->urbanizaciones()->sync([$urbanizacion->id => ['activo' => true]]);

        return Asesor::create([
            'user_id' => $user->id,
            'supervisor_id' => $supervisorId,
            'nombre' => 'Asesor',
            'apellido' => $ci,
            'ci' => $ci,
            'email' => $email,
            'activo' => true,
        ]);
    }

    private function supervisor(string $email, string $ci): SupervisorProfile
    {
        $user = User::factory()->create(['name' => 'Supervisor '.$ci, 'email' => $email]);
        $user->assignRole('supervisor');

        return SupervisorProfile::create([
            'user_id' => $user->id,
            'nombre' => 'Supervisor '.$ci,
            'ci' => $ci,
            'celular' => '70000001',
            'email' => $email,
            'direccion' => 'Oficina',
            'activo' => true,
        ]);
    }
}
