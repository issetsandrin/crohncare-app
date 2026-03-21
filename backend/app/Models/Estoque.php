<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Estoque extends Model
{
    protected $fillable = ['medicamento_id', 'quantidade_atual', 'dose_diaria', 'data_ultimo_reabastecimento'];

    protected $casts = [
        'data_ultimo_reabastecimento' => 'date',
    ];

    public function medicamento()
    {
        return $this->belongsTo(Medicamento::class);
    }
}
