<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Urbanizacion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientePdfTest extends TestCase
{
    use RefreshDatabase;

    public function test_detalle_cliente_muestra_botones_imprimir_y_pdf(): void
    {
        [$admin, $urbanizacion, $cliente] = $this->adminContext();

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->get(route('clientes.show', $cliente))
            ->assertOk()
            ->assertSee('window.print()', false)
            ->assertSee(route('clientes.pdf', $cliente), false)
            ->assertSee(route('clientes.estado-cuenta.pdf', $cliente), false)
            ->assertSee(route('clientes.reservas.pdf', $cliente), false);
    }

    public function test_pdf_ficha_cliente_responde_correctamente(): void
    {
        [$admin, $urbanizacion, $cliente] = $this->adminContext();

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->get(route('clientes.pdf', $cliente))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_pdf_estado_cuenta_responde_correctamente(): void
    {
        [$admin, $urbanizacion, $cliente] = $this->adminContext();

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->get(route('clientes.estado-cuenta.pdf', $cliente))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_pdf_reservas_responde_correctamente(): void
    {
        [$admin, $urbanizacion, $cliente] = $this->adminContext();

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->get(route('clientes.reservas.pdf', $cliente))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_cliente_externo_no_puede_ver_pdf_de_otro_cliente(): void
    {
        $this->seed();

        $clienteUser = User::where('email', 'cliente@impacto.test')->firstOrFail();
        $otroCliente = Cliente::whereKeyNot($clienteUser->cliente_id)->firstOrFail();

        $this->actingAs($clienteUser)
            ->get(route('clientes.pdf', $otroCliente))
            ->assertForbidden();

        $this->actingAs($clienteUser)
            ->get(route('clientes.estado-cuenta.pdf', $otroCliente))
            ->assertForbidden();

        $this->actingAs($clienteUser)
            ->get(route('clientes.reservas.pdf', $otroCliente))
            ->assertForbidden();
    }

    private function adminContext(): array
    {
        $this->seed();

        $admin = User::where('email', 'admin@impacto.test')->firstOrFail();
        $urbanizacion = Urbanizacion::firstOrFail();
        $cliente = Cliente::where('urbanizacion_id', $urbanizacion->id)->firstOrFail();

        return [$admin, $urbanizacion, $cliente];
    }
}
