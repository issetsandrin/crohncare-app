<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExameResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'nome'        => $this->nome,
            'tipo'        => $this->tipo,
            'medico'      => $this->medico,
            'data'        => $this->data?->format('Y-m-d\TH:i'),
            'local'       => $this->local,
            'observacoes' => $this->observacoes,
            'status'      => $this->status,
            'created_at'  => $this->created_at,
        ];
    }
}
