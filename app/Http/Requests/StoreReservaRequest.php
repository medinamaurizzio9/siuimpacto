<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use App\Models\Lote;
use App\Models\Reserva;
use App\Services\CommercialSettingsService;

class StoreReservaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->isMethod('post')
            ? ($this->user()?->can('crear reservas') ?? false)
            : ($this->user() !== null);
    }

    protected function prepareForValidation(): void
    {
        if ($this->user()?->hasRole('vendedor')) {
            $fechaReserva = $this->input('fecha_reserva') ?: now()->toDateString();
            $settings = app(CommercialSettingsService::class);
            $urbanizacionId = null;

            if ($this->filled('lote_id')) {
                $urbanizacionId = Lote::with('manzano')
                    ->find($this->input('lote_id'))
                    ?->manzano
                    ?->urbanizacion_id;
            }

            $this->merge([
                'fecha_reserva' => $fechaReserva,
                'fecha_vencimiento' => $settings
                    ->addBusinessDays(Carbon::parse($fechaReserva), $settings->reservaDiasHabilesAsesor($urbanizacionId ? (int) $urbanizacionId : null))
                    ->toDateString(),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'cliente_id' => ['required', 'exists:clientes,id'],
            'lote_id' => ['required', 'exists:lotes,id'],
            'fecha_reserva' => ['required', 'date'],
            'fecha_vencimiento' => ['required', 'date', 'after_or_equal:fecha_reserva'],
            'monto_reserva' => ['nullable', 'numeric', 'min:0'],
            'estado' => ['nullable', 'in:activa,vencida,cancelada'],
            'tipo_operacion' => ['required', 'in:'.implode(',', Reserva::TIPOS_OPERACION)],
            'observaciones' => ['nullable', 'string'],
            'metodo_pago' => ['nullable', 'in:efectivo,transferencia,QR,banco,otro'],
            'referencia' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'cliente_id.required' => 'Debe seleccionar un cliente válido de la lista.',
            'cliente_id.exists' => 'Debe seleccionar un cliente válido de la lista.',
            'fecha_vencimiento.after_or_equal' => 'La fecha de vencimiento debe ser igual o posterior a la fecha de reserva.',
            'tipo_operacion.required' => 'Selecciona el tipo de operacion de la reserva.',
        ];
    }
}
