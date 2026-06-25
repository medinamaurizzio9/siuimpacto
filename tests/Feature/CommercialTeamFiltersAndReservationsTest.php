<?php

namespace Tests\Feature;

use App\Models\Asesor;
use App\Models\Cliente;
use App\Models\GrupoComercial;
use App\Models\Lote;
use App\Models\Reserva;
use App\Models\Urbanizacion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommercialTeamFiltersAndReservationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_asesores_filtra_por_nombre_email_y_ci(): void
    {
        [$admin, $urbanizacion] = $this->adminContext();

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->get(route('asesores.index', ['buscar' => 'VEN-100']))
            ->assertOk()
            ->assertSee('Asesor de Ventas');

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->get(route('asesores.index', ['buscar' => 'vendedor@impacto.test']))
            ->assertOk()
            ->assertSee('Asesor de Ventas');
    }

    public function test_supervisores_filtra_por_nombre_email_y_estado(): void
    {
        [$admin, $urbanizacion] = $this->adminContext();

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->get(route('supervisores.index', ['buscar' => 'supervisor@impacto.test', 'estado' => 'activo']))
            ->assertOk()
            ->assertSee('Supervisor Comercial');
    }

    public function test_grupos_filtra_por_nombre_y_supervisor(): void
    {
        [$admin, $urbanizacion] = $this->adminContext();
        $supervisor = User::where('email', 'supervisor@impacto.test')->firstOrFail();

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->get(route('grupos-comerciales.index', [
                'buscar' => 'Grupo Norte',
                'supervisor_id' => $supervisor->id,
            ]))
            ->assertOk()
            ->assertSee('Grupo Norte')
            ->assertDontSee('Grupo Sur');
    }

    public function test_asignacion_urbanizaciones_filtra_asesores(): void
    {
        [$admin, $urbanizacion] = $this->adminContext();

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->get(route('urbanizaciones.asignaciones', [
                'buscar' => 'VEN-100',
                'urbanizacion_id' => $urbanizacion->id,
                'solo_activos' => 1,
            ]))
            ->assertOk()
            ->assertSee('Asesor de Ventas');
    }

    public function test_administrador_y_gerente_pueden_cancelar_reserva_y_se_audita_eliminar_reserva(): void
    {
        [$admin, $urbanizacion] = $this->adminContext();
        $gerente = User::where('email', 'gerente@impacto.test')->firstOrFail();
        $reservaAdmin = $this->crearReserva($urbanizacion, $admin, 'CAN-ADMIN');
        $reservaGerente = $this->crearReserva($urbanizacion, $admin, 'CAN-GER');

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->delete(route('reservas.destroy', $reservaAdmin), ['motivo' => 'Prueba admin'])
            ->assertRedirect();

        $this->actingAs($gerente)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->delete(route('reservas.destroy', $reservaGerente), ['motivo' => 'Prueba gerente'])
            ->assertRedirect();

        $this->assertDatabaseHas('reservas', ['id' => $reservaAdmin->id, 'estado' => 'cancelada']);
        $this->assertDatabaseHas('reservas', ['id' => $reservaGerente->id, 'estado' => 'cancelada']);
        $this->assertDatabaseHas('audit_logs', ['modelo' => 'Reserva', 'modelo_id' => $reservaAdmin->id, 'accion' => 'eliminar_reserva']);
        $this->assertDatabaseHas('audit_logs', ['modelo' => 'Reserva', 'modelo_id' => $reservaGerente->id, 'accion' => 'eliminar_reserva']);
    }

    public function test_supervisor_puede_cancelar_reserva_de_su_equipo_y_asesor_no_puede(): void
    {
        $this->seed();
        $supervisor = User::where('email', 'supervisor@impacto.test')->firstOrFail();
        $vendedor = User::where('email', 'vendedor@impacto.test')->firstOrFail();
        $urbanizacion = $vendedor->urbanizacionesAsignadas()->firstOrFail();
        $reserva = $this->crearReserva($urbanizacion, $vendedor, 'CAN-SUP');
        $reservaVendedor = $this->crearReserva($urbanizacion, $vendedor, 'NO-CAN-VEN');

        $this->actingAs($supervisor)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->delete(route('reservas.destroy', $reserva), ['motivo' => 'Seguimiento supervisor'])
            ->assertRedirect();

        $this->actingAs($vendedor)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->delete(route('reservas.destroy', $reservaVendedor), ['motivo' => 'No autorizado'])
            ->assertForbidden();

        $this->assertDatabaseHas('reservas', ['id' => $reserva->id, 'estado' => 'cancelada']);
    }

    public function test_supervisor_no_puede_cancelar_reserva_ajena_por_url(): void
    {
        $this->seed();
        $supervisor = User::where('email', 'supervisor@impacto.test')->firstOrFail();
        $admin = User::where('email', 'admin@impacto.test')->firstOrFail();
        $urbanizacion = $supervisor->urbanizacionesAsignadas()->firstOrFail();
        $reserva = $this->crearReserva($urbanizacion, $admin, 'NO-CAN-AJENA');

        $this->actingAs($supervisor)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->delete(route('reservas.destroy', $reserva), ['motivo' => 'No autorizado'])
            ->assertForbidden();
    }

    public function test_listado_reservas_muestra_columna_asesor_y_filtra_por_asesor_autorizado(): void
    {
        [$admin, $urbanizacion] = $this->adminContext();
        $vendedor = User::where('email', 'vendedor@impacto.test')->firstOrFail();
        $reserva = $this->crearReserva($urbanizacion, $vendedor, 'FILTRO-ASESOR');
        $ajena = $this->crearReserva($urbanizacion, $admin, 'FILTRO-AJENA');

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->get(route('reservas.index', ['usuario_id' => $vendedor->id]))
            ->assertOk()
            ->assertSee('Asesor')
            ->assertSee($reserva->lote->codigo)
            ->assertDontSee($ajena->lote->codigo);
    }

    public function test_asesor_no_puede_usar_filtro_asesor_para_ver_reservas_ajenas(): void
    {
        $this->seed();
        $vendedor = User::where('email', 'vendedor@impacto.test')->firstOrFail();
        $admin = User::where('email', 'admin@impacto.test')->firstOrFail();
        $urbanizacion = $vendedor->urbanizacionesAsignadas()->firstOrFail();
        $propia = $this->crearReserva($urbanizacion, $vendedor, 'PROPIA-FILTRO');
        $ajena = $this->crearReserva($urbanizacion, $admin, 'AJENA-FILTRO');

        $this->actingAs($vendedor)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->get(route('reservas.index', ['usuario_id' => $admin->id]))
            ->assertOk()
            ->assertSee($propia->lote->codigo)
            ->assertDontSee($ajena->lote->codigo)
            ->assertDontSee('name="usuario_id"', false);
    }

    public function test_supervisor_solo_ve_reservas_de_su_equipo(): void
    {
        $this->seed();
        $supervisor = User::where('email', 'supervisor@impacto.test')->firstOrFail();
        $vendedor = User::where('email', 'vendedor@impacto.test')->firstOrFail();
        $urbanizacion = $vendedor->urbanizacionesAsignadas()->firstOrFail();
        $equipo = $this->crearReserva($urbanizacion, $vendedor, 'EQUIPO-VISIBLE');
        $admin = User::where('email', 'admin@impacto.test')->firstOrFail();
        $ajena = $this->crearReserva($urbanizacion, $admin, 'EQUIPO-OCULTO');

        $this->actingAs($supervisor)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->get(route('reservas.index'))
            ->assertOk()
            ->assertSee($equipo->lote->codigo)
            ->assertDontSee($ajena->lote->codigo);
    }

    public function test_asesor_solo_ve_sus_clientes(): void
    {
        $this->seed();
        $vendedor = User::where('email', 'vendedor@impacto.test')->firstOrFail();
        $admin = User::where('email', 'admin@impacto.test')->firstOrFail();
        $urbanizacion = $vendedor->urbanizacionesAsignadas()->firstOrFail();
        $propio = $this->crearCliente($urbanizacion, $vendedor, 'Cliente Propio Asesor', 'CLI-PROP');
        $ajeno = $this->crearCliente($urbanizacion, $admin, 'Cliente Ajeno Asesor', 'CLI-AJENO');

        $this->actingAs($vendedor)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->get(route('clientes.index'))
            ->assertOk()
            ->assertSee($propio->nombre)
            ->assertDontSee($ajeno->nombre)
            ->assertDontSee('name="usuario_id"', false);
    }

    public function test_supervisor_ve_clientes_de_su_equipo_y_propios(): void
    {
        $this->seed();
        $supervisor = User::where('email', 'supervisor@impacto.test')->firstOrFail();
        $vendedor = User::where('email', 'vendedor@impacto.test')->firstOrFail();
        $admin = User::where('email', 'admin@impacto.test')->firstOrFail();
        $urbanizacion = $vendedor->urbanizacionesAsignadas()->firstOrFail();
        $equipo = $this->crearCliente($urbanizacion, $vendedor, 'Cliente Equipo Supervisor', 'CLI-EQUIPO');
        $propio = $this->crearCliente($urbanizacion, $supervisor, 'Cliente Propio Supervisor', 'CLI-SUP');
        $ajeno = $this->crearCliente($urbanizacion, $admin, 'Cliente Oculto Supervisor', 'CLI-OCULTO');

        $this->actingAs($supervisor)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->get(route('clientes.index'))
            ->assertOk()
            ->assertSee($equipo->nombre)
            ->assertSee($propio->nombre)
            ->assertDontSee($ajeno->nombre);
    }

    public function test_admin_ve_todos_los_clientes_y_puede_filtrar_por_asesor(): void
    {
        [$admin, $urbanizacion] = $this->adminContext();
        $vendedor = User::where('email', 'vendedor@impacto.test')->firstOrFail();
        $clienteVendedor = $this->crearCliente($urbanizacion, $vendedor, 'Cliente Filtro Vendedor', 'CLI-FIL-VEN');
        $clienteAdmin = $this->crearCliente($urbanizacion, $admin, 'Cliente Filtro Admin', 'CLI-FIL-ADM');

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->get(route('clientes.index'))
            ->assertOk()
            ->assertSee($clienteVendedor->nombre)
            ->assertSee($clienteAdmin->nombre)
            ->assertSee('name="usuario_id"', false);

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->get(route('clientes.index', ['usuario_id' => $vendedor->id]))
            ->assertOk()
            ->assertSee($clienteVendedor->nombre)
            ->assertDontSee($clienteAdmin->nombre);
    }

    private function adminContext(): array
    {
        $this->seed();

        return [
            User::where('email', 'admin@impacto.test')->firstOrFail(),
            Urbanizacion::firstOrFail(),
        ];
    }

    private function crearReserva(Urbanizacion $urbanizacion, User $user, string $codigoLote): Reserva
    {
        $cliente = Cliente::where('urbanizacion_id', $urbanizacion->id)->firstOrFail();
        $lote = Lote::whereHas('manzano', fn ($query) => $query->where('urbanizacion_id', $urbanizacion->id))
            ->where('estado', 'disponible')
            ->firstOrFail();
        $lote->update(['codigo' => $codigoLote, 'estado' => 'reservado']);

        return Reserva::create([
            'cliente_id' => $cliente->id,
            'lote_id' => $lote->id,
            'usuario_id' => $user->id,
            'fecha_reserva' => now(),
            'fecha_vencimiento' => now()->addDays(5),
            'monto_reserva' => 100,
            'estado' => 'activa',
            'tipo_operacion' => 'contado',
        ]);
    }

    private function crearCliente(Urbanizacion $urbanizacion, User $user, string $nombre, string $documento): Cliente
    {
        return Cliente::create([
            'urbanizacion_id' => $urbanizacion->id,
            'created_by' => $user->id,
            'nombre' => $nombre,
            'documento' => $documento,
            'telefono' => '70000000',
            'email' => strtolower($documento).'@test.local',
        ]);
    }
}
