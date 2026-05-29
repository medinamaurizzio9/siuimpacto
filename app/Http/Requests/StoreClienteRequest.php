<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->isMethod('post')
            ? ($this->user()?->can('crear clientes') ?? false)
            : ($this->user()?->can('editar clientes') ?? false);
    }

    public function rules(): array
    {
        $cliente = $this->route('cliente');
        $clienteId = $cliente?->id;

        $documentoRules = ['nullable', 'string', 'max:80'];

        if (! $this->isMethod('post')) {
            $documentoRules[] = Rule::unique('clientes', 'documento')
                ->where(fn ($query) => $query->where('urbanizacion_id', session('urbanizacion_id')))
                ->ignore($clienteId);
        }

        return [
            'nombre' => ['required', 'string', 'max:255'],
            'documento' => $documentoRules,
            'telefono' => ['nullable', 'string', 'max:80'],
            'email' => ['nullable', 'email', 'max:255'],
            'direccion' => ['nullable', 'string'],
            'lote_id' => ['nullable', 'integer', 'exists:lotes,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'documento.unique' => 'Este cliente ya existe en la urbanizacion actual.',
        ];
    }
}
