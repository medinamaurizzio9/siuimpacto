<?php

namespace App\Services;

use App\Models\CashMovement;
use App\Models\Lote;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class ReceiptQrService
{
    public function __construct(private PublicUrlService $publicUrl)
    {
    }

    public function data(CashMovement $movement, ?Lote $lot): string
    {
        return $this->publicUrl->route('recibos.verificar', ['numero' => $this->number($movement)]);
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
