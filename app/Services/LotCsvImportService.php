<?php

namespace App\Services;

use App\Models\Lote;
use App\Models\Manzano;
use App\Models\Urbanizacion;
use Illuminate\Http\UploadedFile;

class LotCsvImportService
{
    private const HEADERS = ['urbanizacion', 'manzano', 'lote', 'superficie_m2', 'precio_m2', 'precio_total', 'cuota_inicial_tipo', 'cuota_inicial_valor', 'estado', 'coord_x', 'coord_y', 'observaciones'];
    private const LEGACY_HEADERS = ['urbanizacion', 'manzano', 'lote', 'superficie_m2', 'precio_m2', 'precio_total', 'estado', 'coord_x', 'coord_y', 'observaciones'];

    public function parse(UploadedFile|string $file): array
    {
        $path = $file instanceof UploadedFile ? $file->getRealPath() : $file;
        $handle = fopen($path, 'r');
        $headers = fgetcsv($handle) ?: [];
        $headers = array_map(fn ($h) => trim((string) $h), $headers);
        $rows = [];
        $errors = [];
        $line = 1;

        $acceptedHeaders = [self::HEADERS, self::LEGACY_HEADERS];
        $activeHeaders = in_array($headers, $acceptedHeaders, true) ? $headers : self::HEADERS;

        if (! in_array($headers, $acceptedHeaders, true)) {
            $errors[] = 'La cabecera CSV no coincide con el formato requerido.';
        }

        while (($data = fgetcsv($handle)) !== false) {
            $line++;
            $row = array_combine($activeHeaders, array_pad($data, count($activeHeaders), null));
            $row['cuota_inicial_tipo'] = trim((string) ($row['cuota_inicial_tipo'] ?? 'monto')) ?: 'monto';
            $row['cuota_inicial_valor'] = $row['cuota_inicial_valor'] ?? 0;
            $rowErrors = $this->validateRow($row, $line);

            if ($rowErrors) {
                $errors = [...$errors, ...$rowErrors];
            }

            $rows[] = $row;
        }

        fclose($handle);

        return ['rows' => $rows, 'errors' => $errors];
    }

    public function import(array $rows): int
    {
        $count = 0;

        foreach ($rows as $row) {
            $urbanizacion = Urbanizacion::firstOrCreate(
                ['nombre' => trim($row['urbanizacion'])],
                ['estado' => 'activa']
            );
            $manzano = Manzano::firstOrCreate(
                ['urbanizacion_id' => $urbanizacion->id, 'codigo' => trim($row['manzano'])],
                ['nombre' => 'Manzano '.trim($row['manzano']), 'orden' => 0]
            );

            Lote::create([
                'manzano_id' => $manzano->id,
                'codigo' => trim($row['lote']),
                'superficie' => (float) $row['superficie_m2'],
                'precio' => (float) $row['precio_total'],
                'cuota_inicial_tipo' => trim((string) ($row['cuota_inicial_tipo'] ?: 'monto')),
                'cuota_inicial_valor' => (float) ($row['cuota_inicial_valor'] ?? 0),
                'estado' => trim($row['estado']),
                'fila' => 1,
                'columna' => 1,
                'coord_x' => (float) $row['coord_x'],
                'coord_y' => (float) $row['coord_y'],
                'observaciones' => $row['observaciones'],
            ]);
            $count++;
        }

        return $count;
    }

    private function validateRow(array $row, int $line): array
    {
        $errors = [];
        foreach (['urbanizacion', 'manzano', 'lote'] as $field) {
            if (blank($row[$field])) {
                $errors[] = "Linea {$line}: {$field} es obligatorio.";
            }
        }

        foreach (['superficie_m2', 'precio_m2', 'precio_total'] as $field) {
            if (! is_numeric($row[$field]) || (float) $row[$field] < 0) {
                $errors[] = "Linea {$line}: {$field} debe ser numerico y mayor o igual a cero.";
            }
        }

        if (! in_array($row['cuota_inicial_tipo'], Lote::CUOTA_INICIAL_TIPOS, true)) {
            $errors[] = "Linea {$line}: cuota_inicial_tipo invalido.";
        }

        if (! is_numeric($row['cuota_inicial_valor']) || (float) $row['cuota_inicial_valor'] < 0) {
            $errors[] = "Linea {$line}: cuota_inicial_valor debe ser numerico y mayor o igual a cero.";
        }

        if ($row['cuota_inicial_tipo'] === 'porcentaje' && (float) $row['cuota_inicial_valor'] > 100) {
            $errors[] = "Linea {$line}: cuota_inicial_valor no puede ser mayor a 100 cuando el tipo es porcentaje.";
        }

        if (! in_array($row['estado'], Lote::ESTADOS, true)) {
            $errors[] = "Linea {$line}: estado invalido.";
        }

        foreach (['coord_x', 'coord_y'] as $field) {
            if (! is_numeric($row[$field]) || (float) $row[$field] < 0 || (float) $row[$field] > 100) {
                $errors[] = "Linea {$line}: {$field} debe estar entre 0 y 100.";
            }
        }

        $urbanizacion = Urbanizacion::where('nombre', trim((string) $row['urbanizacion']))->first();
        $manzano = $urbanizacion
            ? Manzano::where('urbanizacion_id', $urbanizacion->id)->where('codigo', trim((string) $row['manzano']))->first()
            : null;

        if ($manzano && Lote::where('manzano_id', $manzano->id)->where('codigo', trim((string) $row['lote']))->exists()) {
            $errors[] = "Linea {$line}: el lote ya existe en ese manzano y urbanizacion.";
        }

        return $errors;
    }
}
