<?php

namespace App\Http\Controllers;

use App\Http\Requests\DiarioRequest;
use App\Http\Resources\DiarioResource;
use App\Models\Diario;
use App\Models\Tag;
use Illuminate\Http\Request;

class DiarioController extends Controller
{
    public function index(Request $request)
    {
        $query = auth()->user()->diarios()->orderBy('data', 'desc');

        if ($request->has('mes')) {
            $parts = explode('-', $request->mes);
            $query->whereYear('data', $parts[0])->whereMonth('data', $parts[1]);
        }

        return DiarioResource::collection($query->get());
    }

    public function store(DiarioRequest $request)
    {
        $diario = auth()->user()->diarios()->create($request->validated());
        Tag::sincronizar($diario->sintomas, 'anotacao');
        return new DiarioResource($diario);
    }

    public function update(DiarioRequest $request, Diario $diario)
    {
        $this->authorize('update', $diario);
        $diario->update($request->validated());
        Tag::sincronizar($diario->sintomas, 'anotacao');
        return new DiarioResource($diario);
    }

    public function destroy(Diario $diario)
    {
        $this->authorize('delete', $diario);
        $diario->delete();
        return response()->json(null, 204);
    }

    public function exportar(Request $request)
    {
        $query = auth()->user()->diarios()->orderBy('data', 'desc');

        if ($request->has('mes')) {
            $parts = explode('-', $request->mes);
            $query->whereYear('data', $parts[0])->whereMonth('data', $parts[1]);
        }

        $diarios = $query->get();

        $csv = "Data,Sintomas,Intensidade,Observações\n";
        foreach ($diarios as $d) {
            $csv .= sprintf(
                "%s,\"%s\",%d,\"%s\"\n",
                $d->data->format('d/m/Y'),
                str_replace('"', '""', $d->sintomas),
                $d->intensidade,
                str_replace('"', '""', $d->observacoes ?? '')
            );
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="diario.csv"',
        ]);
    }
}
