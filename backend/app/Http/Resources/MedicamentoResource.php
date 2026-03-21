<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MedicamentoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'dose' => $this->dose,
            'instrucoes' => $this->instrucoes,
            'periodicidade_tipo' => $this->periodicidade_tipo ?? 'diario',
            'periodicidade_valor' => $this->periodicidade_valor,
            'ativo' => $this->ativo,
            'horarios' => $this->horarios->pluck('horario'),
            'estoque' => $this->estoque ? [
                'quantidade_atual' => $this->estoque->quantidade_atual,
                'dose_diaria' => $this->estoque->dose_diaria,
                'data_ultimo_reabastecimento' => $this->estoque->data_ultimo_reabastecimento?->format('Y-m-d'),
            ] : null,
            'dias_restantes' => $this->dias_restantes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
