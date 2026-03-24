<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExameRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome'        => 'required|string|max:255',
            'tipo'        => 'nullable|string|max:255',
            'data'        => 'required|date',
            'local'       => 'nullable|string|max:255',
            'observacoes' => 'nullable|string',
            'status'      => 'nullable|in:agendado,realizado,cancelado',
        ];
    }
}
