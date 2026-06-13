<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class PlanImageService
{
    public function store(UploadedFile $file): array
    {
        if ($this->isPdf($file)) {
            return $this->storePdf($file);
        }

        return [
            'plano_imagen' => $file->store('planos', 'public'),
            'plano_archivo_original' => null,
        ];
    }

    public function delete(?string $imagePath, ?string $originalPath = null): void
    {
        if ($imagePath) {
            Storage::disk('public')->delete($imagePath);
        }

        if ($originalPath) {
            Storage::disk('public')->delete($originalPath);
        }
    }

    private function storePdf(UploadedFile $file): array
    {
        $originalPath = $file->store('planos/originales', 'public');
        $imagePath = 'planos/'.Str::uuid().'.png';

        try {
            $this->convertPdfFirstPageToPng(
                Storage::disk('public')->path($originalPath),
                Storage::disk('public')->path($imagePath)
            );
        } catch (RuntimeException $exception) {
            Storage::disk('public')->delete($originalPath);
            throw $exception;
        }

        return [
            'plano_imagen' => $imagePath,
            'plano_archivo_original' => $originalPath,
        ];
    }

    private function convertPdfFirstPageToPng(string $pdfPath, string $outputPath): void
    {
        if (! class_exists(\Imagick::class)) {
            throw new RuntimeException('No se pudo convertir el PDF. Suba una imagen JPG o PNG en alta resolución.');
        }

        try {
            $image = new \Imagick();
            $image->setResolution(300, 300);
            $image->readImage($pdfPath.'[0]');
            $image->setImageBackgroundColor('white');
            $image = $image->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
            $image->setImageFormat('png');
            $image->setImageCompressionQuality(100);
            $image->stripImage();

            if (! $image->writeImage($outputPath)) {
                throw new RuntimeException('No se pudo convertir el PDF. Suba una imagen JPG o PNG en alta resolución.');
            }

            $image->clear();
            $image->destroy();
        } catch (\Throwable $exception) {
            throw new RuntimeException('No se pudo convertir el PDF. Suba una imagen JPG o PNG en alta resolución.', 0, $exception);
        }
    }

    private function isPdf(UploadedFile $file): bool
    {
        return strtolower($file->getClientOriginalExtension()) === 'pdf'
            || $file->getMimeType() === 'application/pdf';
    }
}
