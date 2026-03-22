<?php

namespace App\Http\Controllers;

use App\Models\Medicamento;
use App\Http\Resources\MedicamentoResource;
use Illuminate\Http\Request;

class EstoqueController extends Controller
{
    public function update(Request $request, Medicamento $medicamento)
    {
        $this->authorize('update', $medicamento);

        $validated = $request->validate([
            'quantidade_atual' => 'required|integer|min:0',
            'dose_diaria' => 'required|integer|min:1',
        ]);

        $medicamento->estoque()->updateOrCreate(
            ['medicamento_id' => $medicamento->id],
            $validated
        );

        $medicamento->load(['horarios', 'estoque']);
        return new MedicamentoResource($medicamento);
    }

    public function reabastecer(Request $request, Medicamento $medicamento)
    {
        $this->authorize('update', $medicamento);

        $validated = $request->validate([
            'quantidade' => 'required|integer|min:1',
        ]);

        $estoque = $medicamento->estoque;
        if ($estoque) {
            $estoque->update([
                'quantidade_atual' => $estoque->quantidade_atual + $validated['quantidade'],
                'data_ultimo_reabastecimento' => now(),
            ]);
        }

        $medicamento->load(['horarios', 'estoque']);
        return new MedicamentoResource($medicamento);
    }
}
