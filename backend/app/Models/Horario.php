<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Horario extends Model
{
    protected $fillable = ['medicamento_id', 'horario'];

    public function medicamento()
    {
        return $this->belongsTo(Medicamento::class);
    }
}
