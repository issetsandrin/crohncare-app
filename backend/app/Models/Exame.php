<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exame extends Model
{
    protected $fillable = ['user_id', 'nome', 'tipo', 'medico', 'data', 'local', 'observacoes', 'status'];

    protected $casts = [
        'data' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
