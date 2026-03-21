<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Crise extends Model
{
    protected $fillable = ['data_hora', 'sintomas', 'intensidade', 'duracao_estimada', 'observacoes'];

    protected $casts = [
        'data_hora' => 'datetime',
        'intensidade' => 'integer',
    ];
}
