<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConsultaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'medico' => 'required|string|max:255',
            'especialidade' => 'nullable|string|max:255',
            'data_hora' => 'required|date',
            'local' => 'nullable|string|max:255',
            'observacoes' => 'nullable|string',
            'status' => 'nullable|in:agendada,realizada,cancelada',
        ];
    }
}
