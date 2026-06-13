<?php

namespace App\Http\Requests;

use App\Models\Lote;
use App\Support\UrbanizacionContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->isMethod('post')
            ? ($this->user()?->can('crear lotes') ?? false)
            : ($this->user()?->can('editar lotes') ?? false);
    }

    public function rules(): array
    {
        $lote = $this->route('lote');
        $loteId = $lote instanceof Lote ? $lote->id : $lote;

        return [
            'manzano_id' => [
                'required',
                Rule::exists('manzanos', 'id')->where('urbanizacion_id', UrbanizacionContext::currentId()),
            ],
            'codigo' => [
                'required',
                'string',
                'max:50',
                Rule::unique('lotes', 'codigo')
                    ->where(fn ($query) => $query->where('manzano_id', $this->input('manzano_id')))
                    ->ignore($loteId),
            ],
            'superficie' => ['required', 'numeric', 'min:0'],
            'precio' => ['required', 'numeric', 'min:0'],
            'cuota_inicial_tipo' => ['required', 'in:monto,porcentaje'],
            'cuota_inicial_valor' => [
                'required',
                'numeric',
                'min:0',
                Rule::when($this->input('cuota_inicial_tipo') === 'porcentaje', ['max:100']),
            ],
            'estado' => ['required', 'in:disponible,reservado,vendido,bloqueado'],
            'fila' => ['required', 'integer', 'min:1'],
            'columna' => ['required', 'integer', 'min:1'],
            'coord_x' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'coord_y' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'observaciones' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'codigo.unique' => 'Ya existe un lote con ese código en este manzano.',
            'manzano_id.exists' => 'El manzano seleccionado no pertenece a la urbanización actual.',
        ];
    }
}
