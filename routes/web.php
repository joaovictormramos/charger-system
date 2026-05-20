<?php

use App\Http\Controllers\ChargerController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/charger/{charger}/configuration', [ChargerController::class, 'configuration']);

Route::livewire('/cartao/{rfidCard:uuid}', 'card.card-show');