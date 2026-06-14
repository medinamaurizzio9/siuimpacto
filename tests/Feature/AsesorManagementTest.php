<?php

namespace Tests\Feature;

use App\Models\Asesor;
use App\Models\SupervisorProfile;
use App\Models\Urbanizacion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
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
            ->assertSee('Importar Excel');

        $this->actingAs($vendedor)
            ->withSession(['urbanizacion_id' => $vendedor->urbanizacionesAsignadas()->firstOrFail()->id])
            ->get(route('asesores.index'))
            ->assertForbidden()
            ->assertDontSee('Importar Excel');
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
}
