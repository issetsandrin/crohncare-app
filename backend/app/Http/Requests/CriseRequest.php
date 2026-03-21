<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CriseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'data_hora' => 'required|date',
            'sintomas' => 'required|string',
            'intensidade' => 'required|integer|between:1,5',
            'duracao_estimada' => 'nullable|string|max:100',
            'observacoes' => 'nullable|string',
        ];
    }
}
