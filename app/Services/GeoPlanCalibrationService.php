<?php

namespace App\Services;

use App\Models\Urbanizacion;
use RuntimeException;

class GeoPlanCalibrationService
{
    public function gpsToPlanPosition(Urbanizacion $urbanizacion, float $latitud, float $longitud): array
    {
        $referencias = $urbanizacion->referencias()
            ->where('activo', true)
            ->whereNotNull('plano_x')
            ->whereNotNull('plano_y')
            ->get(['latitud', 'longitud', 'plano_x', 'plano_y']);

        if ($referencias->count() < 4) {
            throw new RuntimeException('La urbanizacion requiere al menos 4 referencias GPS calibradas.');
        }

        $matrix = [];
        $targetX = [];
        $targetY = [];

        foreach ($referencias as $referencia) {
            $matrix[] = [(float) $referencia->latitud, (float) $referencia->longitud, 1.0];
            $targetX[] = (float) $referencia->plano_x;
            $targetY[] = (float) $referencia->plano_y;
        }

        $coefficientsX = $this->leastSquares($matrix, $targetX);
        $coefficientsY = $this->leastSquares($matrix, $targetY);

        return [
            'x' => round($this->clamp($coefficientsX[0] * $latitud + $coefficientsX[1] * $longitud + $coefficientsX[2]), 2),
            'y' => round($this->clamp($coefficientsY[0] * $latitud + $coefficientsY[1] * $longitud + $coefficientsY[2]), 2),
        ];
    }

    private function leastSquares(array $matrix, array $target): array
    {
        $normal = array_fill(0, 3, array_fill(0, 3, 0.0));
        $right = array_fill(0, 3, 0.0);

        foreach ($matrix as $index => $row) {
            for ($i = 0; $i < 3; $i++) {
                $right[$i] += $row[$i] * $target[$index];
                for ($j = 0; $j < 3; $j++) {
                    $normal[$i][$j] += $row[$i] * $row[$j];
                }
            }
        }

        return $this->solve3x3($normal, $right);
    }

    private function solve3x3(array $matrix, array $right): array
    {
        for ($i = 0; $i < 3; $i++) {
            $pivot = $i;
            for ($row = $i + 1; $row < 3; $row++) {
                if (abs($matrix[$row][$i]) > abs($matrix[$pivot][$i])) {
                    $pivot = $row;
                }
            }

            if (abs($matrix[$pivot][$i]) < 0.000000001) {
                throw new RuntimeException('No se pudo calcular la calibracion GPS con las referencias actuales.');
            }

            if ($pivot !== $i) {
                [$matrix[$i], $matrix[$pivot]] = [$matrix[$pivot], $matrix[$i]];
                [$right[$i], $right[$pivot]] = [$right[$pivot], $right[$i]];
            }

            $factor = $matrix[$i][$i];
            for ($column = $i; $column < 3; $column++) {
                $matrix[$i][$column] /= $factor;
            }
            $right[$i] /= $factor;

            for ($row = 0; $row < 3; $row++) {
                if ($row === $i) {
                    continue;
                }

                $factor = $matrix[$row][$i];
                for ($column = $i; $column < 3; $column++) {
                    $matrix[$row][$column] -= $factor * $matrix[$i][$column];
                }
                $right[$row] -= $factor * $right[$i];
            }
        }

        return $right;
    }

    private function clamp(float $value): float
    {
        return max(0, min(100, $value));
    }
}
