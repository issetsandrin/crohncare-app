<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Diario extends Model
{
    protected $fillable = ['user_id', 'data', 'sintomas', 'intensidade', 'observacoes'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected $casts = [
        'data' => 'date',
        'intensidade' => 'integer',
    ];
}
