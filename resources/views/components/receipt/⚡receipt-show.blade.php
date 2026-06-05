<?php
use App\Models\Transaction;
use Livewire\Component;
use Livewire\Attributes\Layout;

new #[Layout('layouts.app')] class extends Component
{
    public Transaction $transaction;

    public function mount(Transaction $transaction): void
    {
        $this->transaction = $transaction;
    }
};
?>

<div class="min-h-screen bg-white">

    {{-- Header --}}
    <div class="bg-[#FF8400] px-4 pt-5 pb-6">
        <div class="flex items-center justify-between mb-5">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-7 object-contain brightness-0 invert">
            <span class="text-xs text-orange-200">Comprovante</span>
        </div>
    </div>

    <div class="p-4">

        {{-- Ícone de sucesso --}}
        <div class="text-center mb-6">
            <div class="w-14 h-14 bg-green-50 rounded-full flex items-center justify-center mx-auto mb-3">
                <i class="ti ti-check text-3xl text-green-500"></i>
            </div>
            <p class="text-lg font-medium text-gray-800 mb-1">Recarga concluída</p>
            <p class="text-sm text-gray-400">
                {{ $transaction->start_time->format('d \d\e F \d\e Y') }} · {{ $transaction->end_time?->format('H:i') ?? '—' }}
            </p>
        </div>

        {{-- Detalhes --}}
        <div class="bg-gray-50 rounded-xl p-4 mb-4 space-y-3">
            <div class="flex justify-between text-sm">
                <span class="text-gray-400">Estação</span>
                <span class="text-gray-800">{{ $transaction->charger->identifier }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-400">Duração</span>
                <span class="text-gray-800">
                    @if($transaction->end_time)
                        {{ gmdate('H\h i\m', $transaction->end_time->diffInSeconds($transaction->start_time)) }}
                    @else
                        —
                    @endif
                </span>
            </div>
            <div class="flex justify-between text-sm pb-3 border-b border-gray-200">
                <span class="text-gray-400">Energia carregada</span>
                <span class="text-gray-800">{{ number_format($transaction->energy_kwh ?? 0, 2, ',', '.') }} kWh</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-400">Valor consumido</span>
                <span class="text-gray-800">R$ {{ number_format(($transaction->total_cost ?? 0), 2, ',', '.') }}</span>
            </div>
            @if(!$transaction->rfid_card_id)
            <div class="flex justify-between text-sm">
                <span class="text-gray-400">Taxa de plataforma (1%)</span>
                <span class="text-gray-400">R$ {{ number_format((($transaction->total_cost ?? 0) * 0.01) / 100, 2, ',', '.') }}</span>
            </div>
            @endif
            <div class="flex justify-between text-sm font-medium pt-2 border-t border-gray-200">
                <span class="text-gray-800">Total pago</span>
                <span class="text-gray-800">R$ {{ number_format(($transaction->paid_amount ?? 0) / 100, 2, ',', '.') }}</span>
            </div>
        </div>

        {{-- Compartilhar --}}
        <button onclick="navigator.share && navigator.share({title:'Comprovante', text:'Recarga concluída: {{ number_format($transaction->energy_kwh ?? 0, 2, ',', '.') }} kWh por R$ {{ number_format(($transaction->paid_amount ?? 0) / 100, 2, ',', '.') }}'})"
            class="w-full py-4 border border-gray-200 text-gray-600 rounded-xl text-sm font-medium flex items-center justify-center gap-2">
            <i class="ti ti-share text-lg"></i>
            Compartilhar comprovante
        </button>

    </div>
</div>