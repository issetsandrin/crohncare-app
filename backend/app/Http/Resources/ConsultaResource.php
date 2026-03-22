<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConsultaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'medico' => $this->medico,
            'especialidade' => $this->especialidade,
            'data_hora' => $this->data_hora->format('Y-m-d H:i'),
            'local' => $this->local,
            'observacoes' => $this->observacoes,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
