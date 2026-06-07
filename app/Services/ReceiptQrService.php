<?php

namespace App\Services;

use App\Models\CashMovement;
use App\Models\Lote;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class ReceiptQrService
{
    public function data(CashMovement $movement, ?Lote $lot): string
    {
        return implode("\n", [
            'Recibo: '.$this->number($movement),
            'Cliente: '.($movement->cliente?->nombre ?? 'Sin cliente registrado'),
            'Documento: '.($movement->cliente?->documento ?? 'Sin documento'),
            'Urbanización: '.($lot?->manzano?->urbanizacion?->nombre ?? 'No aplica'),
            'Lote: '.($lot ? $lot->manzano->codigo.' / '.$lot->codigo : 'No aplica'),
            'Monto: Bs '.number_format((float) $movement->monto, 2, '.', ''),
            'Fecha: '.($movement->created_at?->format('d/m/Y H:i') ?? $movement->fecha?->format('d/m/Y')),
        ]);
    }

    public function dataUri(CashMovement $movement, ?Lote $lot): string
    {
        $result = (new PngWriter())->write(new QrCode(
            data: $this->data($movement, $lot),
            errorCorrectionLevel: ErrorCorrectionLevel::Medium,
            size: 240,
            margin: 8,
        ));

        return $result->getDataUri();
    }

    public function number(CashMovement $movement): string
    {
        return str_pad((string) $movement->id, 8, '0', STR_PAD_LEFT);
    }
}
