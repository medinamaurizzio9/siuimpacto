<?php

namespace App\Http\Controllers;

use App\Models\CashMovement;
use App\Services\ReceiptQrService;
use App\Services\SystemSettingsService;
use Illuminate\View\View;

class PublicReceiptVerificationController extends Controller
{
    public function __invoke(string $numero, ReceiptQrService $receiptQr, SystemSettingsService $settings): View
    {
        $id = (int) ltrim($numero, '0');
        $movimiento = $id > 0
            ? CashMovement::with([
                'cliente',
                'reserva.lote.manzano.urbanizacion',
                'venta.lote.manzano.urbanizacion',
                'cuota.venta.lote.manzano.urbanizacion',
            ])->find($id)
            : null;

        $lote = $movimiento?->reserva?->lote
            ?? $movimiento?->venta?->lote
            ?? $movimiento?->cuota?->venta?->lote;

        return view('public.recibos-verificar', [
            'movimiento' => $movimiento,
            'lote' => $lote,
            'numero' => $movimiento ? $receiptQr->number($movimiento) : $numero,
            'settings' => $settings->all(),
            'documento' => $this->maskedDocument($movimiento?->cliente?->documento),
        ]);
    }

    private function maskedDocument(?string $document): string
    {
        if (! $document) {
            return 'No registrado';
        }

        $length = strlen($document);

        if ($length <= 4) {
            return str_repeat('*', $length);
        }

        return substr($document, 0, 3).str_repeat('*', max(3, $length - 6)).substr($document, -3);
    }
}
