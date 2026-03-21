<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CriseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'data_hora' => $this->data_hora->format('Y-m-d H:i'),
            'sintomas' => $this->sintomas,
            'intensidade' => $this->intensidade,
            'duracao_estimada' => $this->duracao_estimada,
            'observacoes' => $this->observacoes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
