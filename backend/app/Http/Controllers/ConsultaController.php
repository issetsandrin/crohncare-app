<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConsultaRequest;
use App\Http\Resources\ConsultaResource;
use App\Models\Consulta;

class ConsultaController extends Controller
{
    public function index()
    {
        return ConsultaResource::collection(
            auth()->user()->consultas()->orderBy('data_hora', 'desc')->get()
        );
    }

    public function store(ConsultaRequest $request)
    {
        $consulta = auth()->user()->consultas()->create($request->validated());
        return new ConsultaResource($consulta);
    }

    public function update(ConsultaRequest $request, Consulta $consulta)
    {
        $this->authorize('update', $consulta);
        $consulta->update($request->validated());
        return new ConsultaResource($consulta);
    }

    public function destroy(Consulta $consulta)
    {
        $this->authorize('delete', $consulta);
        $consulta->delete();
        return response()->json(null, 204);
    }
}
