<?php

namespace App\Services;

use App\Models\Asesor;
use App\Models\Cliente;
use App\Models\GrupoComercial;
use App\Models\Reserva;
use App\Models\SupervisorProfile;
use App\Models\Venta;

class DeletionDependencyService
{
    public function forCliente(Cliente $cliente): array
    {
        $dependencies = [];

        $this->addCount($dependencies, 'ventas registradas', $cliente->ventas()->count());
        $this->addCount($dependencies, 'reservas activas', $cliente->reservas()->where('estado', 'activa')->count());
        $this->addCount($dependencies, 'reservas registradas', $cliente->reservas()->where('estado', '<>', 'activa')->count());
        $this->addCount($dependencies, 'cuotas relacionadas', $this->clienteCuotasCount($cliente));

        return $dependencies;
    }

    public function forAsesor(Asesor $asesor): array
    {
        $userId = (int) $asesor->user_id;
        $dependencies = [];

        $this->addCount($dependencies, 'clientes registrados', Cliente::where('created_by', $userId)->count());
        $this->addCount($dependencies, 'reservas creadas', Reserva::where('usuario_id', $userId)->count());
        $this->addCount($dependencies, 'ventas asociadas', Venta::where('user_id', $userId)->count());
        $this->addCount($dependencies, 'asesores asignados a su equipo', Asesor::where('supervisor_id', $userId)->count());

        $grupo = $asesor->grupo;
        if ($grupo) {
            $dependencies[] = [
                'label' => 'pertenece al grupo comercial',
                'count' => null,
                'detail' => $grupo->nombre,
            ];
        } elseif ($asesor->grupo_comercial) {
            $dependencies[] = [
                'label' => 'pertenece al grupo comercial',
                'count' => null,
                'detail' => $asesor->grupo_comercial,
            ];
        }

        return $dependencies;
    }

    public function forSupervisor(SupervisorProfile $supervisor): array
    {
        $userId = (int) $supervisor->user_id;
        $teamUserIds = Asesor::where('supervisor_id', $userId)->pluck('user_id');
        $dependencies = [];

        $this->addCount($dependencies, 'asesores asignados', Asesor::where('supervisor_id', $userId)->count());
        $this->addCount($dependencies, 'grupos comerciales bajo su responsabilidad', GrupoComercial::where('supervisor_id', $userId)->count());
        $this->addCount($dependencies, 'reservas propias', Reserva::where('usuario_id', $userId)->count());

        if ($teamUserIds->isNotEmpty()) {
            $this->addCount($dependencies, 'reservas de su equipo', Reserva::whereIn('usuario_id', $teamUserIds)->count());
        }

        return $dependencies;
    }

    public function message(string $subject, array $dependencies): string
    {
        $lines = ["No se puede eliminar {$subject} porque tiene relaciones activas:"];

        foreach ($dependencies as $dependency) {
            $lines[] = '• '.$this->dependencyLine($dependency);
        }

        return implode("\n", $lines);
    }

    public function hasDependencies(array $dependencies): bool
    {
        return $dependencies !== [];
    }

    private function clienteCuotasCount(Cliente $cliente): int
    {
        return Venta::where('cliente_id', $cliente->id)
            ->withCount('cuotas')
            ->get()
            ->sum('cuotas_count');
    }

    private function addCount(array &$dependencies, string $label, int $count): void
    {
        if ($count > 0) {
            $dependencies[] = [
                'label' => $label,
                'count' => $count,
            ];
        }
    }

    private function dependencyLine(array $dependency): string
    {
        if (($dependency['count'] ?? null) !== null) {
            return $dependency['count'].' '.$dependency['label'];
        }

        return $dependency['label'].': '.$dependency['detail'];
    }
}
