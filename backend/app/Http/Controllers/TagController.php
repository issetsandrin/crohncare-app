<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function index(Request $request)
    {
        $query = Tag::orderBy('nome');

        if ($request->has('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        return response()->json([
            'data' => $query->pluck('nome')
        ]);
    }
}
