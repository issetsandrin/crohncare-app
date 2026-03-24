<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExameRequest;
use App\Http\Resources\ExameResource;
use App\Models\Exame;

class ExameController extends Controller
{
    public function index()
    {
        return ExameResource::collection(
            auth()->user()->exames()->orderBy('data', 'desc')->get()
        );
    }

    public function store(ExameRequest $request)
    {
        $exame = auth()->user()->exames()->create($request->validated());
        return new ExameResource($exame);
    }

    public function update(ExameRequest $request, Exame $exame)
    {
        abort_if($exame->user_id !== auth()->id(), 403);
        $exame->update($request->validated());
        return new ExameResource($exame);
    }
}
