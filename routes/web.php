<?php

use App\Http\Controllers\ChargerController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/charger/{charger}/configuration', [ChargerController::class, 'configuration']);

Route::livewire('/cartao/{rfidCard:uuid}', 'card.card-show');
Route::livewire('/recarregar/{charger:identifier}', 'charger.charger-recharge');
Route::livewire('/sessao/{transaction:uuid}', 'session.session-show');
Route::livewire('/comprovante/{transaction:uuid}', 'receipt.receipt-show')->name('receipt.show');;