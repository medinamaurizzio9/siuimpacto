<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVentaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->isMethod('post')
            ? ($this->user()?->can('crear ventas') ?? false)
            : ($this->user()?->can('editar ventas') ?? $this->user()?->can('crear ventas') ?? false);
    }

    public function rules(): array
    {
        return [
            'lote_id' => ['required', 'exists:lotes,id'],
            'cliente_id' => ['required', 'exists:clientes,id'],
            'fecha_venta' => ['required', 'date'],
            'precio_final' => ['required', 'numeric', 'min:0'],
            'cuota_inicial' => ['nullable', 'numeric', 'min:0'],
            'numero_cuotas' => ['nullable', 'integer', 'min:0'],
            'estado' => ['required', 'in:activa,completada,anulada'],
            'observaciones' => ['nullable', 'string'],
            'metodo_pago' => ['nullable', 'in:efectivo,transferencia,QR,banco,otro'],
            'referencia' => ['nullable', 'string', 'max:255'],
            'admin_confirma_reserva' => ['nullable', 'boolean'],
            'grupo_comercial_id' => ['nullable', 'exists:grupos_comerciales,id'],
            'supervisor_comercial_id' => ['nullable', 'exists:users,id'],
            'supervisor_ventas_id' => ['nullable', 'exists:users,id'],
            'vendedor_id' => ['nullable', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'lote_id.required' => 'Selecciona el lote que se vendera.',
            'cliente_id.required' => 'Selecciona el cliente comprador.',
            'precio_final.min' => 'El precio final no puede ser negativo.',
        ];
    }
}
