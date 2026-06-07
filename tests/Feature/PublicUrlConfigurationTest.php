<?php

namespace Tests\Feature;

use App\Models\CashMovement;
use App\Models\SystemSetting;
use App\Models\Urbanizacion;
use App\Models\User;
use App\Services\PublicUrlService;
use App\Services\ReceiptQrService;
use App\Services\SystemSettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicUrlConfigurationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_base_url_guarda_correctamente(): void
    {
        [$admin, $urbanizacion] = $this->adminContext();

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->put(route('admin.configuracion-general.update'), $this->settingsPayload([
                'public_base_url' => 'https://crm.inmolider.com',
            ]))
            ->assertRedirect();

        $this->assertDatabaseHas('system_settings', [
            'key' => 'public_base_url',
            'value' => 'https://crm.inmolider.com',
        ]);
    }

    public function test_rechaza_url_sin_http_o_https(): void
    {
        [$admin, $urbanizacion] = $this->adminContext();

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacion->id])
            ->from(route('admin.configuracion-general'))
            ->put(route('admin.configuracion-general.update'), $this->settingsPayload([
                'public_base_url' => 'crm.inmolider.com',
            ]))
            ->assertRedirect(route('admin.configuracion-general'))
            ->assertSessionHasErrors('public_base_url');
    }

    public function test_public_url_service_usa_public_base_url_si_existe(): void
    {
        $this->seed();
        app(SystemSettingsService::class)->setMany(['public_base_url' => 'https://crm.inmolider.com']);

        $movement = CashMovement::firstOrFail();

        $this->assertSame(
            'https://crm.inmolider.com/recibos/verificar/'.str_pad((string) $movement->id, 8, '0', STR_PAD_LEFT),
            app(PublicUrlService::class)->route('recibos.verificar', ['numero' => str_pad((string) $movement->id, 8, '0', STR_PAD_LEFT)])
        );
    }

    public function test_public_url_service_usa_app_url_si_public_base_url_esta_vacio(): void
    {
        $this->seed();
        config(['app.url' => 'http://app-url.test']);
        app(SystemSettingsService::class)->setMany(['public_base_url' => '']);

        $this->assertSame('http://app-url.test/demo', app(PublicUrlService::class)->url('/demo'));
    }

    public function test_qr_de_recibo_contiene_ruta_de_verificacion_y_no_texto_plano(): void
    {
        $this->seed();
        app(SystemSettingsService::class)->setMany(['public_base_url' => 'https://crm.inmolider.com']);

        $movement = CashMovement::with('cliente')->firstOrFail();
        $payload = app(ReceiptQrService::class)->data($movement, null);

        $this->assertSame('https://crm.inmolider.com/recibos/verificar/'.str_pad((string) $movement->id, 8, '0', STR_PAD_LEFT), $payload);
        $this->assertStringNotContainsString($movement->cliente?->nombre ?? '', $payload);
        $this->assertStringNotContainsString('Monto:', $payload);
    }

    public function test_verificacion_de_recibo_abre_sin_login_y_muestra_valido(): void
    {
        $this->seed();

        $movement = CashMovement::firstOrFail();

        $this->get(route('recibos.verificar', ['numero' => str_pad((string) $movement->id, 8, '0', STR_PAD_LEFT)]))
            ->assertOk()
            ->assertSee('RECIBO VÁLIDO')
            ->assertSee(str_pad((string) $movement->id, 8, '0', STR_PAD_LEFT));
    }

    public function test_recibo_anulado_muestra_recibo_anulado(): void
    {
        $this->seed();

        $movement = CashMovement::firstOrFail();
        $movement->update(['estado' => 'anulado']);

        $this->get(route('recibos.verificar', ['numero' => str_pad((string) $movement->id, 8, '0', STR_PAD_LEFT)]))
            ->assertOk()
            ->assertSee('RECIBO ANULADO');
    }

    public function test_link_publico_de_urbanizacion_usa_public_base_url(): void
    {
        $this->seed();
        app(SystemSettingsService::class)->setMany(['public_base_url' => 'http://54.123.45.67']);

        $urbanizacion = Urbanizacion::where('estado', 'activa')->firstOrFail();

        $this->assertSame(
            'http://54.123.45.67/u/'.$urbanizacion->slug,
            app(PublicUrlService::class)->route('disponibilidad.urbanizacion', ['slug' => $urbanizacion->slug])
        );

        $this->get(route('disponibilidad.urbanizacion', ['slug' => $urbanizacion->slug]))
            ->assertOk()
            ->assertSee('http://54.123.45.67/u/'.$urbanizacion->slug)
            ->assertSee('QR de disponibilidad publica')
            ->assertSee('data:image/png;base64,', false);
    }

    private function adminContext(): array
    {
        $this->seed();

        return [
            User::where('email', 'admin@impacto.test')->firstOrFail(),
            Urbanizacion::where('estado', 'activa')->firstOrFail(),
        ];
    }

    private function settingsPayload(array $overrides = []): array
    {
        return array_merge([
            'system_name' => 'INMOLIDER CRM',
            'system_subtitle' => 'Sistema Integral de Terrenos',
            'public_base_url' => '',
            'company_name' => 'INMOLIDER',
            'primary_color' => '#0f766e',
            'secondary_color' => '#0f2530',
        ], $overrides);
    }
}
