<?php

namespace App\Http\Controllers;

use App\Models\Medicamento;
use App\Models\RegistroUso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RegistroUsoController extends Controller
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
            $fotoPath = $request->file('foto')->store('registros_uso/' . auth()->id(), 'public');
        }

        $registro = RegistroUso::create([
            'user_id'        => auth()->id(),
            'medicamento_id' => $medicamento->id,
            'foto_path'      => $fotoPath,
            'usado_em'       => now(),
        ]);

        return response()->json([
            'id'       => $registro->id,
            'usado_em' => $registro->usado_em,
            'foto_url' => $fotoPath ? Storage::url($fotoPath) : null,
        ], 201);
    }

    public function index(Medicamento $medicamento)
    {
        abort_if($medicamento->user_id !== auth()->id(), 403);

        $registros = $medicamento->registrosUso()
            ->orderByDesc('usado_em')
            ->get()
            ->map(fn($r) => [
                'id'       => $r->id,
                'usado_em' => $r->usado_em,
                'foto_url' => $r->foto_path ? Storage::url($r->foto_path) : null,
            ]);

        return response()->json($registros);
    }
}
