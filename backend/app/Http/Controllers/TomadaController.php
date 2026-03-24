<?php

namespace App\Http\Controllers;

use App\Models\Medicamento;
use App\Models\Tomada;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TomadaController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'medicamento_id' => 'required|integer',
            'foto'           => 'nullable|file|image|max:10240',
        ]);

        $medicamento = Medicamento::findOrFail($data['medicamento_id']);
        abort_if($medicamento->user_id !== auth()->id(), 403);

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('tomadas/' . auth()->id(), 'public');
        }

        $tomada = Tomada::create([
            'user_id'        => auth()->id(),
            'medicamento_id' => $medicamento->id,
            'foto_path'      => $fotoPath,
            'tomado_em'      => now(),
        ]);

        return response()->json([
            'id'         => $tomada->id,
            'tomado_em'  => $tomada->tomado_em,
            'foto_url'   => $fotoPath ? Storage::url($fotoPath) : null,
        ], 201);
    }

    public function index(Medicamento $medicamento)
    {
        abort_if($medicamento->user_id !== auth()->id(), 403);

        $tomadas = $medicamento->tomadas()
            ->orderByDesc('tomado_em')
            ->get()
            ->map(fn($t) => [
                'id'        => $t->id,
                'tomado_em' => $t->tomado_em,
                'foto_url'  => $t->foto_path ? Storage::url($t->foto_path) : null,
            ]);

        return response()->json($tomadas);
    }
}
