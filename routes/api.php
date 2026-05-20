<?php

use App\Http\Controllers\OcppController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/ocpp', [OcppController::class, 'handle']);
