<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Diario extends Model
{
    protected $fillable = ['data', 'sintomas', 'intensidade', 'observacoes'];

    protected $casts = [
        'data' => 'date',
        'intensidade' => 'integer',
    ];
}
