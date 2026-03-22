<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AvisoController extends Controller
{
    public function index()
    {
        $avisos = auth()->user()->avisos()
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($avisos);
    }

    public function marcarLido($id)
    {
        $aviso = auth()->user()->avisos()->findOrFail($id);
        $aviso->update(['lido' => true]);

        return response()->json($aviso);
    }

    public function marcarTodosLidos()
    {
        auth()->user()->avisos()->where('lido', false)->update(['lido' => true]);

        return response()->json(['message' => 'Todos marcados como lidos']);
    }

    public function naoLidos()
    {
        $count = auth()->user()->avisos()->where('lido', false)->count();

        return response()->json(['count' => $count]);
    }
}
