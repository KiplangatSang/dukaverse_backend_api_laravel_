<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/{any}', [AppController::class, 'index'])->where('any', '.*');
Route::middleware('auth:sanctum')->post('/logout', function (Request $request) {
    $request->user()->tokens->each(function ($token, $key) {
        $token->delete();
    });

    return response()->json(['message' => 'Logged out successfully.']);
});
