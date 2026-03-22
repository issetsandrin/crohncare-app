<?php

namespace App\Http\Controllers;

use App\Http\Requests\CriseRequest;
use App\Http\Resources\CriseResource;
use App\Models\Crise;
use App\Models\Tag;

class CriseController extends Controller
{
    public function index()
    {
        return CriseResource::collection(
            auth()->user()->crises()->orderBy('data_hora', 'desc')->get()
        );
    }

    public function store(CriseRequest $request)
    {
        $crise = auth()->user()->crises()->create($request->validated());
        Tag::sincronizar($crise->sintomas, 'crise');
        return new CriseResource($crise);
    }

    public function update(CriseRequest $request, Crise $crise)
    {
        $this->authorize('update', $crise);
        $crise->update($request->validated());
        Tag::sincronizar($crise->sintomas, 'crise');
        return new CriseResource($crise);
    }

    public function destroy(Crise $crise)
    {
        $this->authorize('delete', $crise);
        $crise->delete();
        return response()->json(null, 204);
    }
}
