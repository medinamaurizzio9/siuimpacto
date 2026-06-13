<?php

namespace Tests\Feature;

use App\Models\Urbanizacion;
use App\Models\User;
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
            ->assertSee('Editar');

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
}
