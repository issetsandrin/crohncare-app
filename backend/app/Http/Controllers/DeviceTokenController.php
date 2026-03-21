<?php

namespace App\Http\Controllers;

use App\Models\DeviceToken;
use Illuminate\Http\Request;

class DeviceTokenController extends Controller
{
    public function store(Request $request)
    {
        $request->validate(['token' => 'required|string']);

        DeviceToken::updateOrCreate(
            ['token' => $request->token],
            ['user_id' => $request->user()->id]
        );

        return response()->json(['message' => 'Token registrado']);
    }

    public function destroy(Request $request)
    {
        $request->validate(['token' => 'required|string']);

        DeviceToken::where('token', $request->token)->delete();

        return response()->json(['message' => 'Token removido']);
    }
}
