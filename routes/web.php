<?php

use App\Http\Controllers\ChargerController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::livewire('/cartao/{rfidCard:uuid}', 'card.card-show');
Route::livewire('/recarregar/{charger:identifier}', 'charger.charger-recharge');
Route::livewire('/sessao/{transaction:uuid}', 'session.session-show');
Route::livewire('/comprovante/{transaction:uuid}', 'receipt.receipt-show')->name('receipt.show');

Route::middleware('auth')->prefix('admin')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::livewire('/carregadores', 'admin.chargers.charger-index')->name('admin.chargers');
    Route::livewire('/transacoes', 'admin.transactions.transaction-index')->name('admin.transactions');
    Route::livewire('/', 'admin.dashboard')->name('admin.dashboard');
    Route::livewire('/cartoes', 'admin.rfid-cards.rfid-card-index')->name('admin.rfid-cards');
    Route::get('/charger/{charger}/configuration', [ChargerController::class, 'configuration']);
});

require __DIR__.'/auth.php';
