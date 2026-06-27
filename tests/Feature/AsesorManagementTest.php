<?php

namespace Tests\Feature;

use App\Models\Asesor;
use App\Models\Cliente;
use App\Models\Lote;
use App\Models\Reserva;
use App\Models\SupervisorProfile;
use App\Models\Urbanizacion;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AsesorManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_puede_crear_asesor_con_usuario(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@impacto.test')->firstOrFail();
        $urbanizacion = Urbanizacion::firstOrFail();

        $this->actingAs($admin)
            ->post(route('asesores.store'), $this->payload(['urbanizaciones' => [$urbanizacion->id]]))
            ->assertRedirect(route('asesores.index'));

        $asesor = Asesor::where('ci', '998877')->firstOrFail();
        $this->assertNotNull($asesor->user);
        $this->assertSame('asesor.nuevo@impacto.test', $asesor->user->email);
        $this->assertTrue($asesor->user->hasRole('vendedor'));
        $this->assertTrue($asesor->user->must_change_password);
        $this->assertDatabaseHas('urbanizacion_user', [
            'user_id' => $asesor->user_id,
            'urbanizacion_id' => $urbanizacion->id,
            'activo' => true,
        ]);
    }

    public function test_contrasena_inicial_es_ci(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@impacto.test')->firstOrFail();
        $this->actingAs($admin)->post(route('asesores.store'), $this->payload());

        $asesor = Asesor::where('ci', '998877')->firstOrFail();
        $this->assertTrue(Hash::check('998877', $asesor->user->password));
    }

    public function test_asesor_debe_cambiar_contrasena_en_primer_login(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@impacto.test')->firstOrFail();
        $this->actingAs($admin)->post(route('asesores.store'), $this->payload());
        $this->post(route('logout'));

        $this->post(route('login.store'), [
            'email' => 'asesor.nuevo@impacto.test',
            'password' => '998877',
        ])->assertRedirect(route('password.change'));
    }

    public function test_supervisor_puede_crear_asesor_solo_en_su_equipo(): void
    {
        $this->seed();

        $supervisor = User::where('email', 'supervisor@impacto.test')->firstOrFail();
        $urbanizacion = $supervisor->urbanizacionesAsignadas()->firstOrFail();

        $this->actingAs($supervisor)
            ->post(route('asesores.store'), $this->payload([
                'email' => 'asesor.supervisor@impacto.test',
                'ci' => '776655',
                'supervisor_id' => User::where('email', 'admin@impacto.test')->firstOrFail()->id,
                'urbanizaciones' => [$urbanizacion->id],
            ]))
            ->assertRedirect(route('asesores.index'));

        $asesor = Asesor::where('ci', '776655')->firstOrFail();
        $this->assertSame($supervisor->id, $asesor->supervisor_id);
    }

    public function test_supervisor_no_puede_asignar_urbanizacion_no_permitida(): void
    {
        $this->seed();

        $supervisor = User::where('email', 'supervisor@impacto.test')->firstOrFail();
        $noPermitida = Urbanizacion::whereKeyNot($supervisor->urbanizacionesAsignadas()->pluck('urbanizaciones.id'))->firstOrFail();

        $this->actingAs($supervisor)
            ->post(route('asesores.store'), $this->payload([
                'email' => 'asesor.bloqueado@impacto.test',
                'ci' => '665544',
                'urbanizaciones' => [$noPermitida->id],
            ]))
            ->assertSessionHasErrors('urbanizaciones.0');
    }

    public function test_reset_de_contrasena_activa_must_change_password(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@impacto.test')->firstOrFail();
        $this->actingAs($admin)->post(route('asesores.store'), $this->payload());
        $asesor = Asesor::where('ci', '998877')->firstOrFail();
        $asesor->user->update([
            'password' => Hash::make('otra-clave'),
            'must_change_password' => false,
        ]);

        $this->actingAs($admin)
            ->post(route('asesores.reset-password', $asesor))
            ->assertRedirect();

        $asesor->user->refresh();
        $this->assertTrue($asesor->user->must_change_password);
        $this->assertTrue(Hash::check($asesor->ci, $asesor->user->password));
    }

    public function test_actualizar_asesor_sin_cambiar_urbanizaciones_no_duplica_pivot(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@impacto.test')->firstOrFail();
        $urbanizacion = Urbanizacion::firstOrFail();

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->post(route('asesores.store'), $this->payload([
                'email' => 'asesor.update.same@test.local',
                'ci' => 'ASE-UPD-SAME',
                'urbanizaciones' => [$urbanizacion->id],
            ]))
            ->assertRedirect(route('asesores.index'));

        $asesor = Asesor::where('ci', 'ASE-UPD-SAME')->firstOrFail();

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->put(route('asesores.update', $asesor), $this->payload([
                'email' => 'asesor.update.same@test.local',
                'ci' => 'ASE-UPD-SAME',
                'urbanizaciones' => [$urbanizacion->id],
                'activo' => '1',
            ]))
            ->assertRedirect(route('asesores.index'));

        $this->assertSame(1, DB::table('urbanizacion_user')
            ->where('user_id', $asesor->user_id)
            ->where('urbanizacion_id', $urbanizacion->id)
            ->count());
    }

    public function test_actualizar_asesor_cambiando_urbanizaciones_sin_duplicar_pivot(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@impacto.test')->firstOrFail();
        $urbanizacionInicial = Urbanizacion::firstOrFail();
        $urbanizacionNueva = Urbanizacion::whereKeyNot($urbanizacionInicial->id)->firstOrFail();

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacionInicial->id])
            ->post(route('asesores.store'), $this->payload([
                'email' => 'asesor.update.change@test.local',
                'ci' => 'ASE-UPD-CHG',
                'urbanizaciones' => [$urbanizacionInicial->id],
            ]))
            ->assertRedirect(route('asesores.index'));

        $asesor = Asesor::where('ci', 'ASE-UPD-CHG')->firstOrFail();

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacionInicial->id])
            ->put(route('asesores.update', $asesor), $this->payload([
                'email' => 'asesor.update.change@test.local',
                'ci' => 'ASE-UPD-CHG',
                'urbanizaciones' => [$urbanizacionNueva->id],
                'activo' => '1',
            ]))
            ->assertRedirect(route('asesores.index'));

        $this->assertDatabaseHas('urbanizacion_user', [
            'user_id' => $asesor->user_id,
            'urbanizacion_id' => $urbanizacionNueva->id,
            'activo' => true,
        ]);
        $this->assertDatabaseMissing('urbanizacion_user', [
            'user_id' => $asesor->user_id,
            'urbanizacion_id' => $urbanizacionInicial->id,
        ]);
    }

    public function test_actualizar_asesor_marcando_lider_no_duplica_urbanizaciones(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@impacto.test')->firstOrFail();
        $urbanizacion = Urbanizacion::firstOrFail();

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->post(route('asesores.store'), $this->payload([
                'email' => 'asesor.update.leader@test.local',
                'ci' => 'ASE-UPD-LID',
                'urbanizaciones' => [$urbanizacion->id],
            ]))
            ->assertRedirect(route('asesores.index'));

        $asesor = Asesor::where('ci', 'ASE-UPD-LID')->firstOrFail();

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->put(route('asesores.update', $asesor), $this->payload([
                'email' => 'asesor.update.leader@test.local',
                'ci' => 'ASE-UPD-LID',
                'urbanizaciones' => [$urbanizacion->id],
                'is_team_leader' => '1',
                'activo' => '1',
            ]))
            ->assertRedirect(route('asesores.index'));

        $asesor->refresh();
        $this->assertTrue($asesor->is_team_leader);
        $this->assertTrue($asesor->user->fresh()->hasRole('supervisor'));
        $this->assertSame(1, DB::table('urbanizacion_user')
            ->where('user_id', $asesor->user_id)
            ->where('urbanizacion_id', $urbanizacion->id)
            ->count());
    }

    public function test_actualizar_asesor_desmarcando_lider_no_duplica_urbanizaciones(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@impacto.test')->firstOrFail();
        $urbanizacion = Urbanizacion::firstOrFail();

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->post(route('asesores.store'), $this->payload([
                'email' => 'asesor.update.unleader@test.local',
                'ci' => 'ASE-UPD-UNL',
                'urbanizaciones' => [$urbanizacion->id],
                'is_team_leader' => '1',
            ]))
            ->assertRedirect(route('asesores.index'));

        $asesor = Asesor::where('ci', 'ASE-UPD-UNL')->firstOrFail();

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->put(route('asesores.update', $asesor), $this->payload([
                'email' => 'asesor.update.unleader@test.local',
                'ci' => 'ASE-UPD-UNL',
                'urbanizaciones' => [$urbanizacion->id],
                'activo' => '1',
            ]))
            ->assertRedirect(route('asesores.index'));

        $asesor->refresh();
        $this->assertFalse($asesor->is_team_leader);
        $this->assertFalse($asesor->user->fresh()->hasRole('supervisor'));
        $this->assertSame(1, DB::table('urbanizacion_user')
            ->where('user_id', $asesor->user_id)
            ->where('urbanizacion_id', $urbanizacion->id)
            ->count());
    }

    public function test_actualizar_asesor_reactiva_relacion_inactiva_sin_duplicate_entry(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@impacto.test')->firstOrFail();
        $urbanizacion = Urbanizacion::firstOrFail();

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->post(route('asesores.store'), $this->payload([
                'email' => 'asesor.update.inactive@test.local',
                'ci' => 'ASE-UPD-INA',
                'urbanizaciones' => [$urbanizacion->id],
            ]))
            ->assertRedirect(route('asesores.index'));

        $asesor = Asesor::where('ci', 'ASE-UPD-INA')->firstOrFail();

        DB::table('urbanizacion_user')
            ->where('user_id', $asesor->user_id)
            ->where('urbanizacion_id', $urbanizacion->id)
            ->update(['activo' => false]);

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->put(route('asesores.update', $asesor), $this->payload([
                'email' => 'asesor.update.inactive@test.local',
                'ci' => 'ASE-UPD-INA',
                'urbanizaciones' => [$urbanizacion->id],
                'activo' => '1',
            ]))
            ->assertRedirect(route('asesores.index'));

        $this->assertDatabaseHas('urbanizacion_user', [
            'user_id' => $asesor->user_id,
            'urbanizacion_id' => $urbanizacion->id,
            'activo' => true,
        ]);
        $this->assertSame(1, DB::table('urbanizacion_user')
            ->where('user_id', $asesor->user_id)
            ->where('urbanizacion_id', $urbanizacion->id)
            ->count());
    }

    public function test_admin_puede_marcar_asesor_como_lider_de_equipo(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@impacto.test')->firstOrFail();
        $urbanizacion = Urbanizacion::firstOrFail();

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->post(route('asesores.store'), $this->payload([
                'email' => 'lider.equipo@test.local',
                'ci' => 'LIDER-100',
                'is_team_leader' => '1',
                'urbanizaciones' => [$urbanizacion->id],
            ]))
            ->assertRedirect(route('asesores.index'));

        $asesor = Asesor::where('ci', 'LIDER-100')->firstOrFail();

        $this->assertTrue($asesor->is_team_leader);
        $this->assertTrue($asesor->team_leader_role_assigned);
        $this->assertTrue($asesor->user->hasRole('supervisor'));
        $this->assertTrue($asesor->user->hasRole('vendedor'));

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->get(route('asesores.index'))
            ->assertOk()
            ->assertSee('Rol equipo')
            ->assertSee('Lider/Supervisor');
    }

    public function test_asesor_lider_ve_clientes_y_reservas_de_su_equipo_y_puede_cancelar(): void
    {
        $this->seed();

        $urbanizacion = Urbanizacion::firstOrFail();
        $lider = $this->crearAsesorUsuario('lider.visible@test.local', 'LIDER-VIS', null, true, $urbanizacion);
        $asesorEquipo = $this->crearAsesorUsuario('asesor.equipo.lider@test.local', 'ASE-LID', $lider->id, false, $urbanizacion);
        $admin = User::where('email', 'admin@impacto.test')->firstOrFail();
        $clienteEquipo = $this->crearCliente($urbanizacion, $asesorEquipo, 'Cliente Equipo Lider', 'CLI-LIDER');
        $clienteAjeno = $this->crearCliente($urbanizacion, $admin, 'Cliente Ajeno Lider', 'CLI-AJENO-LIDER');
        $reservaEquipo = $this->crearReserva($urbanizacion, $clienteEquipo, $asesorEquipo, 'RES-LIDER');
        $reservaAjena = $this->crearReserva($urbanizacion, $clienteAjeno, $admin, 'RES-AJENA-LIDER');

        $this->actingAs($lider)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->get(route('clientes.index'))
            ->assertOk()
            ->assertSee($clienteEquipo->nombre)
            ->assertDontSee($clienteAjeno->nombre);

        $this->actingAs($lider)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->get(route('reservas.index'))
            ->assertOk()
            ->assertSee($reservaEquipo->lote->codigo)
            ->assertDontSee($reservaAjena->lote->codigo);

        $this->actingAs($lider)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->delete(route('reservas.destroy', $reservaEquipo), ['motivo' => 'Gestion del lider'])
            ->assertRedirect();

        $this->assertDatabaseHas('reservas', ['id' => $reservaEquipo->id, 'estado' => 'cancelada']);
    }

    public function test_asesor_lider_aparece_como_supervisor_seleccionable_en_grupos(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@impacto.test')->firstOrFail();
        $urbanizacion = Urbanizacion::firstOrFail();
        $lider = $this->crearAsesorUsuario('lider.grupo@test.local', 'LIDER-GRP', null, true, $urbanizacion);

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->get(route('grupos-comerciales.create'))
            ->assertOk()
            ->assertSee($lider->name);
    }

    public function test_asesor_lider_aparece_en_listado_de_supervisores(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@impacto.test')->firstOrFail();
        $urbanizacion = Urbanizacion::firstOrFail();
        $lider = $this->crearAsesorUsuario('lider.listado@test.local', 'LIDER-LIST', null, true, $urbanizacion);

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->get(route('supervisores.index'))
            ->assertOk()
            ->assertSee($lider->email)
            ->assertSee('Asesor lider');
    }

    public function test_al_desmarcar_lider_pierde_rol_supervisor_si_fue_asignado_por_liderazgo(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@impacto.test')->firstOrFail();
        $urbanizacion = Urbanizacion::firstOrFail();

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->post(route('asesores.store'), $this->payload([
                'email' => 'lider.remover@test.local',
                'ci' => 'LIDER-REM',
                'is_team_leader' => '1',
                'urbanizaciones' => [$urbanizacion->id],
            ]))
            ->assertRedirect(route('asesores.index'));

        $asesor = Asesor::where('ci', 'LIDER-REM')->firstOrFail();
        $this->assertTrue($asesor->user->hasRole('supervisor'));

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->put(route('asesores.update', $asesor), $this->payload([
                'email' => 'lider.remover@test.local',
                'ci' => 'LIDER-REM',
                'urbanizaciones' => [$urbanizacion->id],
                'activo' => '1',
            ]))
            ->assertRedirect(route('asesores.index'));

        $asesor->refresh();
        $this->assertFalse($asesor->is_team_leader);
        $this->assertFalse($asesor->team_leader_role_assigned);
        $this->assertFalse($asesor->user->fresh()->hasRole('supervisor'));
        $this->assertTrue($asesor->user->fresh()->hasRole('vendedor'));
    }

    public function test_al_desmarcar_asesor_lider_desaparece_del_listado_de_supervisores_si_no_era_original(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@impacto.test')->firstOrFail();
        $urbanizacion = Urbanizacion::firstOrFail();

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->post(route('asesores.store'), $this->payload([
                'email' => 'lider.ocultar@test.local',
                'ci' => 'LIDER-OCU',
                'is_team_leader' => '1',
                'urbanizaciones' => [$urbanizacion->id],
            ]))
            ->assertRedirect(route('asesores.index'));

        $asesor = Asesor::where('ci', 'LIDER-OCU')->firstOrFail();

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->get(route('supervisores.index'))
            ->assertOk()
            ->assertSee('lider.ocultar@test.local');

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->put(route('asesores.update', $asesor), $this->payload([
                'email' => 'lider.ocultar@test.local',
                'ci' => 'LIDER-OCU',
                'urbanizaciones' => [$urbanizacion->id],
                'activo' => '1',
            ]))
            ->assertRedirect(route('asesores.index'));

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->get(route('supervisores.index'))
            ->assertOk()
            ->assertDontSee('lider.ocultar@test.local');
    }

    public function test_al_desmarcar_lider_no_pierde_rol_supervisor_original(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@impacto.test')->firstOrFail();
        $urbanizacion = Urbanizacion::firstOrFail();
        $user = User::factory()->create(['name' => 'Supervisor Original Asesor', 'email' => 'supervisor.original.asesor@test.local']);
        $user->assignRole(['vendedor', 'supervisor']);
        $user->urbanizacionesAsignadas()->syncWithoutDetaching([$urbanizacion->id => ['activo' => true]]);
        $asesor = Asesor::create([
            'user_id' => $user->id,
            'nombre' => 'Supervisor',
            'apellido' => 'Original',
            'ci' => 'SUP-ORIG-ASE',
            'email' => $user->email,
            'activo' => true,
            'is_team_leader' => true,
            'team_leader_role_assigned' => false,
        ]);

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->put(route('asesores.update', $asesor), [
                ...$this->payload([
                    'nombre' => 'Supervisor',
                    'apellido' => 'Original',
                    'email' => $user->email,
                    'ci' => 'SUP-ORIG-ASE',
                    'urbanizaciones' => [$urbanizacion->id],
                    'activo' => '1',
                ]),
            ])
            ->assertRedirect(route('asesores.index'));

        $this->assertFalse($asesor->fresh()->is_team_leader);
        $this->assertTrue($user->fresh()->hasRole('supervisor'));
    }

    public function test_asesor_lider_no_duplica_usuario_en_listado_de_supervisores(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@impacto.test')->firstOrFail();
        $urbanizacion = Urbanizacion::firstOrFail();
        $user = User::factory()->create(['name' => 'Supervisor Duplicado', 'email' => 'supervisor.duplicado@test.local']);
        $user->assignRole(['vendedor', 'supervisor']);
        $user->urbanizacionesAsignadas()->syncWithoutDetaching([$urbanizacion->id => ['activo' => true]]);

        SupervisorProfile::create([
            'user_id' => $user->id,
            'nombre' => 'Supervisor Duplicado',
            'ci' => 'SUP-DUP',
            'celular' => '70001122',
            'email' => $user->email,
            'direccion' => 'Oficina central',
            'activo' => true,
        ]);

        Asesor::create([
            'user_id' => $user->id,
            'nombre' => 'Supervisor',
            'apellido' => 'Duplicado',
            'ci' => 'ASE-DUP',
            'email' => $user->email,
            'activo' => true,
            'is_team_leader' => true,
            'team_leader_role_assigned' => false,
        ]);

        $response = $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->get(route('supervisores.index'))
            ->assertOk()
            ->assertSee('Supervisor Duplicado');

        $this->assertSame(1, substr_count($response->getContent(), 'supervisor.duplicado@test.local'));
    }

    public function test_admin_ve_boton_importar_y_vendedor_no_lo_ve(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@impacto.test')->firstOrFail();
        $vendedor = User::where('email', 'vendedor@impacto.test')->firstOrFail();
        $urbanizacion = Urbanizacion::firstOrFail();

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->get(route('asesores.index'))
            ->assertOk()
            ->assertSee('Importar Excel')
            ->assertSee('Eliminar');

        $this->actingAs($vendedor)
            ->withSession(['urbanizacion_id' => $vendedor->urbanizacionesAsignadas()->firstOrFail()->id])
            ->get(route('asesores.index'))
            ->assertForbidden()
            ->assertDontSee('Importar Excel')
            ->assertDontSee('Eliminar');
    }

    public function test_administrador_puede_eliminar_asesor_sin_historial(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@impacto.test')->firstOrFail();
        $urbanizacion = Urbanizacion::firstOrFail();

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->post(route('asesores.store'), $this->payload([
                'email' => 'asesor.eliminar@test.local',
                'ci' => 'ASE-DEL',
                'grupo_comercial' => null,
                'grupo_comercial_id' => null,
            ]))
            ->assertRedirect(route('asesores.index'));

        $asesor = Asesor::where('ci', 'ASE-DEL')->firstOrFail();
        $userId = $asesor->user_id;

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->delete(route('asesores.destroy', $asesor))
            ->assertRedirect()
            ->assertSessionHas('status', 'Usuario eliminado correctamente.');

        $this->assertDatabaseMissing('users', ['id' => $userId]);
        $this->assertDatabaseMissing('asesores', ['id' => $asesor->id]);
        $this->assertDatabaseHas('audit_logs', [
            'modelo' => 'User',
            'modelo_id' => $userId,
            'accion' => 'eliminar_asesor',
        ]);
    }

    public function test_administrador_no_elimina_asesor_con_relaciones_y_muestra_detalle(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@impacto.test')->firstOrFail();
        $urbanizacion = Urbanizacion::firstOrFail();

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->post(route('asesores.store'), $this->payload(['email' => 'asesor.historial@test.local', 'ci' => 'ASE-HIS']))
            ->assertRedirect(route('asesores.index'));

        $asesor = Asesor::where('ci', 'ASE-HIS')->firstOrFail();
        Venta::create([
            'lote_id' => Lote::firstOrFail()->id,
            'cliente_id' => Cliente::firstOrFail()->id,
            'user_id' => $asesor->user_id,
            'fecha_venta' => now()->toDateString(),
            'precio_final' => 10000,
            'cuota_inicial' => 1000,
            'numero_cuotas' => 0,
            'estado' => 'activa',
        ]);

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->delete(route('asesores.destroy', $asesor))
            ->assertRedirect()
            ->assertSessionHasErrors('delete');

        $this->assertDatabaseHas('users', ['id' => $asesor->user_id]);
        $this->assertDatabaseHas('asesores', ['id' => $asesor->id, 'activo' => true]);
        $this->assertStringContainsString('ventas asociadas', session('errors')->first('delete'));
    }

    public function test_usuario_no_admin_recibe_403_al_eliminar_asesor(): void
    {
        $this->seed();
        $vendedor = User::where('email', 'vendedor@impacto.test')->firstOrFail();
        $asesor = Asesor::firstOrFail();
        $urbanizacionId = $vendedor->urbanizacionesAsignadas()->firstOrFail()->id;

        $this->actingAs($vendedor)
            ->withSession(['urbanizacion_id' => $urbanizacionId])
            ->delete(route('asesores.destroy', $asesor))
            ->assertForbidden();
    }

    public function test_admin_importa_asesor_y_supervisor_desde_equipo_comercial(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@impacto.test')->firstOrFail();
        $urbanizacion = Urbanizacion::firstOrFail();

        $file = $this->csv([
            'nombre,email,password,rol,estado,telefono,ci,urbanizacion,supervisor',
            "Super Equipo,super.equipo@example.com,12345678,supervisor,activo,77722222,CI-SUP-IMP,{$urbanizacion->nombre},",
            "Asesor Equipo,asesor.equipo@example.com,12345678,vendedor,activo,77711111,CI-ASE-IMP,{$urbanizacion->nombre},Super Equipo",
        ]);

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->post(route('asesores.import.store'), ['archivo' => $file])
            ->assertRedirect(route('asesores.import'))
            ->assertSessionHas('status');

        $supervisor = User::where('email', 'super.equipo@example.com')->firstOrFail();
        $asesorUser = User::where('email', 'asesor.equipo@example.com')->firstOrFail();

        $this->assertTrue($supervisor->hasRole('supervisor'));
        $this->assertTrue($asesorUser->hasRole('vendedor'));
        $this->assertTrue($supervisor->must_change_password);
        $this->assertTrue($asesorUser->must_change_password);
        $this->assertTrue(Hash::check('12345678', $asesorUser->password));
        $this->assertDatabaseHas('supervisor_profiles', [
            'user_id' => $supervisor->id,
            'nombre' => 'Super Equipo',
            'ci' => 'CI-SUP-IMP',
        ]);
        $this->assertDatabaseHas('asesores', [
            'user_id' => $asesorUser->id,
            'supervisor_id' => $supervisor->id,
            'nombre' => 'Asesor',
            'apellido' => 'Equipo',
            'ci' => 'CI-ASE-IMP',
        ]);
        $this->assertTrue($asesorUser->urbanizacionesAsignadas()->whereKey($urbanizacion->id)->exists());
    }

    public function test_importador_lee_csv_excel_utf8_con_bom_headers_y_urbanizacion_sin_case_sensitive(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@impacto.test')->firstOrFail();
        $urbanizacion = Urbanizacion::firstOrFail();

        $file = $this->csv([
            "\xEF\xBB\xBF Nombre ; Email ; Password ; Rol ; Estado ; Telefono ; CI ; Urbanizacion ; Supervisor ",
            "Asesor Bom;asesor.bom@example.com;12345678;vendedor;activo;77733333;CI-BOM;".mb_strtoupper($urbanizacion->nombre).";",
        ], 'asesores-bom.csv');

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->post(route('asesores.import.store'), ['archivo' => $file])
            ->assertRedirect(route('asesores.import'))
            ->assertSessionHas('status', 'Usuarios creados: 1. Usuarios omitidos: 0. Errores: 0.');

        $user = User::where('email', 'asesor.bom@example.com')->firstOrFail();
        $this->assertTrue($user->urbanizacionesAsignadas()->whereKey($urbanizacion->id)->exists());
        $this->assertDatabaseHas('asesores', [
            'user_id' => $user->id,
            'nombre' => 'Asesor',
            'apellido' => 'Bom',
            'ci' => 'CI-BOM',
        ]);
    }

    public function test_importar_equipo_comercial_email_duplicado_muestra_error(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@impacto.test')->firstOrFail();

        $file = $this->csv([
            'nombre,email,password,rol,estado,telefono,ci,urbanizacion,supervisor',
            'Duplicado,admin@impacto.test,12345678,vendedor,activo,,,,',
        ]);

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => Urbanizacion::firstOrFail()->id])
            ->post(route('asesores.import.store'), ['archivo' => $file])
            ->assertRedirect(route('asesores.import'))
            ->assertSessionHas('import_errors', fn ($errors) => in_array('Fila 2: El email ya existe.', $errors, true));
    }

    public function test_importar_equipo_comercial_rol_invalido_muestra_error(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@impacto.test')->firstOrFail();

        $file = $this->csv([
            'nombre,email,password,rol,estado,telefono,ci,urbanizacion,supervisor',
            'Rol Malo,rol.malo.equipo@example.com,12345678,gerente,activo,,,,',
        ]);

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => Urbanizacion::firstOrFail()->id])
            ->post(route('asesores.import.store'), ['archivo' => $file])
            ->assertRedirect(route('asesores.import'))
            ->assertSessionHas('import_errors', fn ($errors) => in_array('Fila 2: Rol no válido.', $errors, true));
    }

    public function test_no_admin_recibe_403_al_importar_equipo_comercial(): void
    {
        $this->seed();
        $vendedor = User::where('email', 'vendedor@impacto.test')->firstOrFail();
        $urbanizacionId = $vendedor->urbanizacionesAsignadas()->firstOrFail()->id;

        $this->actingAs($vendedor)->withSession(['urbanizacion_id' => $urbanizacionId])->get(route('asesores.import'))->assertForbidden();
        $this->actingAs($vendedor)->withSession(['urbanizacion_id' => $urbanizacionId])->post(route('asesores.import.store'), ['archivo' => $this->csv(['nombre,email,password,rol,estado,telefono,ci,urbanizacion,supervisor'])])->assertForbidden();
        $this->actingAs($vendedor)->withSession(['urbanizacion_id' => $urbanizacionId])->get(route('asesores.template'))->assertForbidden();
    }

    private function payload(array $overrides = []): array
    {
        return array_replace([
            'nombre' => 'Asesor',
            'apellido' => 'Nuevo',
            'ci' => '998877',
            'celular' => '70000001',
            'email' => 'asesor.nuevo@impacto.test',
            'grupo_comercial' => 'Equipo Norte',
            'supervisor_id' => null,
            'urbanizaciones' => [Urbanizacion::firstOrFail()->id],
            'activo' => '1',
        ], $overrides);
    }

    private function csv(array $lines, string $name = 'asesores.csv'): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'asesores-test-');
        file_put_contents($path, implode("\n", $lines));

        return new UploadedFile($path, $name, 'text/csv', null, true);
    }

    private function crearAsesorUsuario(string $email, string $ci, ?int $supervisorId, bool $lider, Urbanizacion $urbanizacion): User
    {
        $user = User::factory()->create(['name' => 'Usuario '.$ci, 'email' => $email]);
        $user->assignRole('vendedor');
        if ($lider) {
            $user->assignRole('supervisor');
        }
        $user->urbanizacionesAsignadas()->syncWithoutDetaching([$urbanizacion->id => ['activo' => true]]);

        Asesor::create([
            'user_id' => $user->id,
            'supervisor_id' => $supervisorId,
            'nombre' => 'Usuario',
            'apellido' => $ci,
            'ci' => $ci,
            'email' => $email,
            'activo' => true,
            'is_team_leader' => $lider,
            'team_leader_role_assigned' => $lider,
        ]);

        return $user;
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

    private function crearReserva(Urbanizacion $urbanizacion, Cliente $cliente, User $user, string $codigoLote): Reserva
    {
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
}
