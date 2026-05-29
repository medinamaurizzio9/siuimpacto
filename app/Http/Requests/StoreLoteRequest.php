<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
        return [
            'manzano_id' => ['required', 'exists:manzanos,id'],
            'codigo' => ['required', 'string', 'max:50'],
            'superficie' => ['required', 'numeric', 'min:0'],
            'precio' => ['required', 'numeric', 'min:0'],
            'estado' => ['required', 'in:disponible,reservado,vendido,bloqueado'],
            'fila' => ['required', 'integer', 'min:1'],
            'columna' => ['required', 'integer', 'min:1'],
            'coord_x' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'coord_y' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'observaciones' => ['nullable', 'string'],
        ];
    }
}
