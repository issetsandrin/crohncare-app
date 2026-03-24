<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MedicamentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome' => 'required|string|max:255',
            'dose' => 'required|string|max:100',
            'instrucoes' => 'nullable|string',
            'periodicidade_tipo' => 'required|string|in:diario,dias_semana,intervalo,sob_demanda,ciclo',
            'periodicidade_valor' => 'nullable|array',
            'periodicidade_valor.dias' => 'nullable|array',
            'periodicidade_valor.dias.*' => 'integer|min:0|max:6',
            'periodicidade_valor.intervalo' => 'nullable|integer|min:1',
            'periodicidade_valor.ciclo' => 'nullable|array|min:2',
            'periodicidade_valor.ciclo.*' => 'required|string|max:100',
            'ativo'               => 'boolean',
            'exige_comprovacao'   => 'nullable|boolean',
            'horarios' => 'required|array|min:1',
            'horarios.*' => 'required|date_format:H:i',
            'estoque' => 'required|array',
            'estoque.quantidade_atual' => 'required|integer|min:0',
            'estoque.dose_diaria' => 'required|integer|min:1',
        ];
    }
}
