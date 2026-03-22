<?php

namespace App\Http\Controllers;

use App\Http\Requests\MedicamentoRequest;
use App\Http\Resources\MedicamentoResource;
use App\Models\Medicamento;
use Illuminate\Support\Facades\DB;

class MedicamentoController extends Controller
{
    public function index()
    {
        $medicamentos = auth()->user()->medicamentos()->with(['horarios', 'estoque'])->get();
        return MedicamentoResource::collection($medicamentos);
    }

    public function store(MedicamentoRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $medicamento = auth()->user()->medicamentos()->create(
                $request->only(['nome', 'dose', 'instrucoes', 'periodicidade_tipo', 'periodicidade_valor', 'ativo'])
            );

            foreach ($request->horarios as $horario) {
                $medicamento->horarios()->create(['horario' => $horario]);
            }

            $medicamento->estoque()->create($request->estoque);

            $medicamento->load(['horarios', 'estoque']);
            return new MedicamentoResource($medicamento);
        });
    }

    public function show(Medicamento $medicamento)
    {
        $this->authorize('view', $medicamento);
        $medicamento->load(['horarios', 'estoque']);
        return new MedicamentoResource($medicamento);
    }

    public function update(MedicamentoRequest $request, Medicamento $medicamento)
    {
        $this->authorize('update', $medicamento);

        return DB::transaction(function () use ($request, $medicamento) {
            $medicamento->update($request->only(['nome', 'dose', 'instrucoes', 'periodicidade_tipo', 'periodicidade_valor', 'ativo']));

            $medicamento->horarios()->delete();
            foreach ($request->horarios as $horario) {
                $medicamento->horarios()->create(['horario' => $horario]);
            }

            if ($request->has('estoque')) {
                $medicamento->estoque()->updateOrCreate(
                    ['medicamento_id' => $medicamento->id],
                    $request->estoque
                );
            }

            $medicamento->load(['horarios', 'estoque']);
            return new MedicamentoResource($medicamento);
        });
    }

    public function destroy(Medicamento $medicamento)
    {
        $this->authorize('delete', $medicamento);
        $medicamento->delete();
        return response()->json(null, 204);
    }
}
