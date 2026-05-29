<?php

namespace Tests\Feature;

use App\Models\Asesor;
use App\Models\Urbanizacion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
