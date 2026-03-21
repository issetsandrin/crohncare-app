<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $fillable = ['nome', 'tipo'];

    public static function sincronizar(string $sintomas, string $tipo): void
    {
        $tags = array_filter(array_map('trim', explode(',', $sintomas)));

        foreach ($tags as $nome) {
            static::firstOrCreate(
                ['nome' => $nome, 'tipo' => $tipo]
            );
        }
    }
}
