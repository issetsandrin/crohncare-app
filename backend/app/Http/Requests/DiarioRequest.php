<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DiarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'data' => 'required|date',
            'sintomas' => 'required|string',
            'intensidade' => 'required|integer|between:1,5',
            'observacoes' => 'nullable|string',
        ];
    }
}
