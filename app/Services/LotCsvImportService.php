<?php

namespace App\Services;

use App\Models\Lote;
use App\Models\Manzano;
use App\Models\Urbanizacion;
use Illuminate\Http\UploadedFile;

class LotCsvImportService
{
    private const HEADERS = ['urbanizacion', 'manzano', 'lote', 'superficie_m2', 'precio_m2', 'precio_total', 'estado', 'coord_x', 'coord_y', 'observaciones'];

    public function parse(UploadedFile|string $file): array
    {
        $path = $file instanceof UploadedFile ? $file->getRealPath() : $file;
        $handle = fopen($path, 'r');
        $headers = fgetcsv($handle) ?: [];
        $headers = array_map(fn ($h) => trim((string) $h), $headers);
        $rows = [];
        $errors = [];
        $line = 1;

        if ($headers !== self::HEADERS) {
            $errors[] = 'La cabecera CSV no coincide con el formato requerido.';
        }

        while (($data = fgetcsv($handle)) !== false) {
            $line++;
            $row = array_combine(self::HEADERS, array_pad($data, count(self::HEADERS), null));
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
