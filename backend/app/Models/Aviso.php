<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Aviso extends Model
{
    protected $fillable = ['user_id', 'titulo', 'mensagem', 'lido'];

    protected $casts = [
        'lido' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
