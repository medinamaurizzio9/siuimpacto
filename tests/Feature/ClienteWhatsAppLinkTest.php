<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\CommercialSetting;
use App\Models\Urbanizacion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClienteWhatsAppLinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_listado_de_clientes_muestra_link_whatsapp_con_prefijo_bolivia(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@impacto.test')->firstOrFail();
        $urbanizacion = Urbanizacion::firstOrFail();
        $cliente = Cliente::where('urbanizacion_id', $urbanizacion->id)->firstOrFail();
        $cliente->update(['telefono' => '690-334 12']);

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->get(route('clientes.index'))
            ->assertOk()
            ->assertSee('690-334 12')
            ->assertSee('href="https://wa.me/59169033412"', false)
            ->assertSee('target="_blank"', false)
            ->assertSee('class="fab fa-whatsapp"', false)
            ->assertSee('Abrir conversacion WhatsApp');
    }

    public function test_detalle_cliente_usa_mensaje_predeterminado_de_whatsapp(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@impacto.test')->firstOrFail();
        $urbanizacion = Urbanizacion::firstOrFail();
        $cliente = Cliente::where('urbanizacion_id', $urbanizacion->id)->firstOrFail();
        $cliente->update(['telefono' => '591 69033412']);
        CommercialSetting::updateOrCreate(
            ['key' => 'whatsapp_mensaje_predeterminado'],
            ['value' => 'Hola {{nombre}}, te escribimos al {{telefono}}']
        );

        $expectedText = rawurlencode('Hola '.$cliente->nombre.', te escribimos al '.$cliente->telefono);

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->get(route('clientes.show', $cliente))
            ->assertOk()
            ->assertSee('href="https://wa.me/59169033412?text='.$expectedText.'"', false)
            ->assertSee('class="fab fa-whatsapp"', false);
    }
}
