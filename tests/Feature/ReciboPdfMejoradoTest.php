<?php

namespace Tests\Feature;

use App\Models\CashMovement;
use App\Models\User;
use App\Services\ReceiptQrService;
use App\Services\SystemSettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReciboPdfMejoradoTest extends TestCase
{
    use RefreshDatabase;

    public function test_boton_recibo_abre_en_nueva_pestana(): void
    {
        [$admin, $movimiento, $urbanizacionId] = $this->contexto();

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacionId])
            ->get(route('caja.index'))
            ->assertOk()
            ->assertSee('href="'.route('pdf.recibo', $movimiento).'" target="_blank"', false);
    }

    public function test_pdf_recibo_responde_correctamente(): void
    {
        [$admin, $movimiento, $urbanizacionId] = $this->contexto();

        $this->actingAs($admin)
            ->withSession(['urbanizacion_id' => $urbanizacionId])
            ->get(route('pdf.recibo', $movimiento))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_recibo_de_reserva_contiene_condiciones_comerciales(): void
    {
        $this->seed();

        $movimiento = CashMovement::where('concepto', 'reserva')->firstOrFail();
        $movimiento->load([
            'cliente',
            'user',
            'reserva.lote.manzano.urbanizacion',
            'venta.lote.manzano.urbanizacion',
            'cuota.venta.lote.manzano.urbanizacion',
        ]);
        $lote = $movimiento->reserva?->lote ?? $movimiento->venta?->lote ?? $movimiento->cuota?->venta?->lote;

        $html = view('pdf.recibo', [
            'movimiento' => $movimiento,
            'lote' => $lote,
            'qrDataUri' => app(ReceiptQrService::class)->dataUri($movimiento, $lote),
            'numeroRecibo' => app(ReceiptQrService::class)->number($movimiento),
            'settings' => app(SystemSettingsService::class)->all(),
        ])->render();

        $this->assertStringContainsString('CONDICIONES DE RESERVA', $html);
        $this->assertStringContainsString('5 días hábiles', $html);
        $this->assertStringContainsString('reservas mayores a Bs 100', $html);
        $this->assertStringContainsString('data:image/png;base64,', $html);
        $this->assertStringContainsString('Comprobante generado por el sistema', $html);
    }

    public function test_qr_contiene_url_publica_de_verificacion(): void
    {
        $this->seed();

        $movimiento = CashMovement::with('cliente', 'reserva.lote.manzano.urbanizacion')->where('concepto', 'reserva')->firstOrFail();
        $data = app(ReceiptQrService::class)->data($movimiento, $movimiento->reserva?->lote);

        $this->assertStringContainsString('/recibos/verificar/'.str_pad((string) $movimiento->id, 8, '0', STR_PAD_LEFT), $data);
        $this->assertStringNotContainsString('Cliente: '.$movimiento->cliente->nombre, $data);
        $this->assertStringNotContainsString('Monto: Bs '.number_format((float) $movimiento->monto, 2, '.', ''), $data);
    }

    private function contexto(): array
    {
        $this->seed();

        $admin = User::where('email', 'admin@impacto.test')->firstOrFail();
        $movimiento = CashMovement::with('venta.lote.manzano', 'reserva.lote.manzano', 'cuota.venta.lote.manzano')->firstOrFail();
        $urbanizacionId = $movimiento->venta?->lote?->manzano?->urbanizacion_id
            ?? $movimiento->reserva?->lote?->manzano?->urbanizacion_id
            ?? $movimiento->cuota?->venta?->lote?->manzano?->urbanizacion_id;

        return [$admin, $movimiento, $urbanizacionId];
    }
}
