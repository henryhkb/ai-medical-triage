<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TriageController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/triage', [TriageController::class, 'analyze'])->middleware('throttle:20,1');
Route::get('/triage/history', [TriageController::class, 'history']);


