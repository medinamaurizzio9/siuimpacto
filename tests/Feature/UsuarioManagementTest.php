<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Lote;
use App\Models\Urbanizacion;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UsuarioManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Urbanizacion $urbanizacion;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $this->admin = User::where('email', 'admin@impacto.test')->firstOrFail();
        $this->urbanizacion = Urbanizacion::firstOrFail();
    }

    public function test_rutas_de_usuarios_responden_para_administrador(): void
    {
        $usuario = User::where('email', 'gerente@impacto.test')->firstOrFail();

        $this->actingAs($this->admin)
            ->withSession(['urbanizacion_id' => $this->urbanizacion->id])
            ->get(route('admin.usuarios'))
            ->assertOk()
            ->assertSee(route('admin.usuarios.create'), false)
            ->assertSee(route('admin.usuarios.edit', $usuario), false)
            ->assertSee('Nuevo usuario')
            ->assertSee('Editar')
            ->assertSee('Eliminar');

        $this->actingAs($this->admin)
            ->withSession(['urbanizacion_id' => $this->urbanizacion->id])
            ->get(route('admin.usuarios.create'))
            ->assertOk()
            ->assertSee('Guardar usuario');

        $this->actingAs($this->admin)
            ->withSession(['urbanizacion_id' => $this->urbanizacion->id])
            ->get(route('admin.usuarios.edit', $usuario))
            ->assertOk()
            ->assertSee('Actualizar usuario');
    }

    public function test_administrador_puede_crear_usuario(): void
    {
        $this->actingAs($this->admin)
            ->withSession(['urbanizacion_id' => $this->urbanizacion->id])
            ->post(route('admin.usuarios.store'), [
                'name' => 'Nuevo Vendedor',
                'email' => 'nuevo.vendedor@test.local',
                'password' => 'Password123',
                'password_confirmation' => 'Password123',
                'rol' => 'vendedor',
            ])
            ->assertRedirect(route('admin.usuarios'))
            ->assertSessionHas('status', 'Usuario creado correctamente.');

        $user = User::where('email', 'nuevo.vendedor@test.local')->firstOrFail();

        $this->assertTrue(Hash::check('Password123', $user->password));
        $this->assertTrue($user->hasRole('vendedor'));
        $this->assertDatabaseHas('audit_logs', [
            'modelo' => 'User',
            'modelo_id' => $user->id,
            'accion' => 'crear_usuario',
        ]);
    }

    public function test_administrador_puede_editar_usuario_sin_cambiar_password(): void
    {
        $usuario = User::where('email', 'vendedor@impacto.test')->firstOrFail();
        $passwordAnterior = $usuario->password;

        $this->actingAs($this->admin)
            ->withSession(['urbanizacion_id' => $this->urbanizacion->id])
            ->put(route('admin.usuarios.update', $usuario), [
                'name' => 'Vendedor Actualizado',
                'email' => 'vendedor.actualizado@test.local',
                'password' => '',
                'password_confirmation' => '',
                'rol' => 'gerente',
            ])
            ->assertRedirect(route('admin.usuarios'))
            ->assertSessionHas('status', 'Usuario actualizado correctamente.');

        $usuario->refresh();

        $this->assertSame('Vendedor Actualizado', $usuario->name);
        $this->assertSame('vendedor.actualizado@test.local', $usuario->email);
        $this->assertSame($passwordAnterior, $usuario->password);
        $this->assertTrue($usuario->hasRole('gerente'));
        $this->assertDatabaseHas('audit_logs', [
            'modelo' => 'User',
            'modelo_id' => $usuario->id,
            'accion' => 'editar_usuario',
        ]);
    }

    public function test_vendedor_y_gerente_reciben_403(): void
    {
        foreach (['vendedor@impacto.test', 'gerente@impacto.test'] as $email) {
            $user = User::where('email', $email)->firstOrFail();
            $urbanizacionId = $user->urbanizacionesAsignadas()->first()?->id ?? $this->urbanizacion->id;

            $this->actingAs($user)
                ->withSession(['urbanizacion_id' => $urbanizacionId])
                ->get(route('admin.usuarios.create'))
                ->assertForbidden();

            $this->actingAs($user)
                ->withSession(['urbanizacion_id' => $urbanizacionId])
                ->post(route('admin.usuarios.store'), [
                    'name' => 'No Autorizado',
                    'email' => 'no.autorizado@test.local',
                    'password' => 'Password123',
                    'password_confirmation' => 'Password123',
                    'rol' => 'vendedor',
                ])
                ->assertForbidden();
        }
    }

    public function test_administrador_puede_eliminar_usuario_sin_historial(): void
    {
        $usuario = User::factory()->create([
            'name' => 'Usuario Sin Historial',
            'email' => 'sin.historial@test.local',
            'estado' => 'activo',
        ]);
        $usuario->assignRole('vendedor');

        $this->actingAs($this->admin)
            ->withSession(['urbanizacion_id' => $this->urbanizacion->id])
            ->delete(route('admin.usuarios.destroy', $usuario))
            ->assertRedirect(route('admin.usuarios'))
            ->assertSessionHas('status', 'Usuario eliminado correctamente.');

        $this->assertDatabaseMissing('users', ['id' => $usuario->id]);
        $this->assertDatabaseHas('audit_logs', [
            'modelo' => 'User',
            'modelo_id' => $usuario->id,
            'accion' => 'eliminar_usuario',
        ]);
    }

    public function test_administrador_desactiva_usuario_con_venta_asociada(): void
    {
        $usuario = User::factory()->create([
            'name' => 'Usuario Con Venta',
            'email' => 'con.venta@test.local',
            'estado' => 'activo',
        ]);
        $usuario->assignRole('vendedor');
        Venta::create([
            'lote_id' => Lote::firstOrFail()->id,
            'cliente_id' => Cliente::firstOrFail()->id,
            'user_id' => $usuario->id,
            'fecha_venta' => now()->toDateString(),
            'precio_final' => 10000,
            'cuota_inicial' => 1000,
            'numero_cuotas' => 0,
            'estado' => 'activa',
        ]);

        $this->actingAs($this->admin)
            ->withSession(['urbanizacion_id' => $this->urbanizacion->id])
            ->delete(route('admin.usuarios.destroy', $usuario))
            ->assertRedirect(route('admin.usuarios'))
            ->assertSessionHas('status', 'El usuario tiene registros asociados, por seguridad fue desactivado.');

        $this->assertDatabaseHas('users', ['id' => $usuario->id, 'estado' => 'inactivo']);
        $this->assertDatabaseHas('audit_logs', [
            'modelo' => 'User',
            'modelo_id' => $usuario->id,
            'accion' => 'desactivar_usuario',
        ]);
    }

    public function test_vendedor_no_ve_boton_eliminar_y_recibe_403_al_intentar_eliminar(): void
    {
        $vendedor = User::where('email', 'vendedor@impacto.test')->firstOrFail();
        $usuario = User::where('email', 'gerente@impacto.test')->firstOrFail();
        $urbanizacionId = $vendedor->urbanizacionesAsignadas()->first()?->id ?? $this->urbanizacion->id;

        $this->actingAs($vendedor)
            ->withSession(['urbanizacion_id' => $urbanizacionId])
            ->get(route('admin.usuarios'))
            ->assertForbidden()
            ->assertDontSee('Eliminar');

        $this->actingAs($vendedor)
            ->withSession(['urbanizacion_id' => $urbanizacionId])
            ->delete(route('admin.usuarios.destroy', $usuario))
            ->assertForbidden();
    }
}
