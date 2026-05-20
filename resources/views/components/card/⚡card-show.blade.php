<?php
use App\Models\RfidCard;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;

new #[Layout('layouts.app')] class extends Component
{
    public RfidCard $rfidCard;

    public string $tab = 'recharge';

    public function mount(RfidCard $rfidCard): void
    {
        $this->rfidCard = $rfidCard;
    }

    public function with(): array
    {
        $transactions = $this->rfidCard
            ->transactions()
            ->whereNotNull('end_time')
            ->latest('end_time')
            ->get();

        return compact('transactions');
    }
};
?>

<div class="min-h-screen bg-white">
        {{-- Header --}}
        <div class="bg-[#FF8400] px-4 pt-5 pb-6">
            <div class="flex items-center justify-between mb-5">
                <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-7 object-contain brightness-0 invert">
                <i class="ti ti-bolt text-orange-100 text-xl"></i>
            </div>
            <p class="text-sm text-orange-200 mb-1">Saldo disponível</p>
            <p class="text-4xl font-medium text-white tracking-tight">
                R$ {{ number_format($rfidCard->balance / 100, 2, ',', '.') }}
            </p>
            <div class="flex items-center gap-1 mt-3">
                <i class="ti ti-credit-card text-orange-200 text-sm"></i>
                <span class="text-xs text-orange-200">•••• •••• {{ strtoupper(substr($rfidCard->uuid, -4)) }}</span>
            </div>
        </div>

        {{-- Tabs --}}
        <div class="flex border-b border-gray-200">
            <button wire:click="$set('tab', 'recharge')"
                class="flex-1 py-3 text-sm font-medium border-b-2 transition-colors
                    {{ $tab === 'recharge' ? 'border-[#FF8400] text-[#FF8400]' : 'border-transparent text-gray-400' }}">
                Recarregar
            </button>
            <button wire:click="$set('tab', 'history')"
                class="flex-1 py-3 text-sm font-medium border-b-2 transition-colors
                    {{ $tab === 'history' ? 'border-[#FF8400] text-[#FF8400]' : 'border-transparent text-gray-400' }}">
                Histórico
            </button>
        </div>

        {{-- Aba Recarregar --}}
        @if($tab === 'recharge')
        <div class="p-4">
            <p class="text-sm text-gray-500 mb-3">Escolha um valor para recarregar</p>
            {{-- valores e pix vêm na próxima etapa --}}
            <p class="text-center text-gray-400 text-sm py-8">Em breve</p>
        </div>
        @endif

        {{-- Aba Histórico --}}
        @if($tab === 'history')
        <div class="p-4 flex flex-col gap-3">
            @forelse($transactions as $t)
            <div class="bg-gray-50 rounded-xl px-4 py-3">
                <div class="flex justify-between items-start mb-1">
                    <span class="text-sm font-medium text-gray-800">{{ $t->charger->name ?? $t->charger->identifier }}</span>
                    <span class="text-xs text-gray-400">{{ $t->end_time->format('d/m/Y') }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-xs text-gray-400">{{ number_format($t->energy_kwh, 2, ',', '.') }} kWh</span>
                    <span class="text-sm font-medium text-gray-800">R$ {{ number_format($t->total_cost / 100, 2, ',', '.') }}</span>
                </div>
            </div>
            @empty
            <p class="text-center text-gray-400 text-sm py-8">Nenhuma recarga ainda.</p>
            @endforelse
        </div>
        @endif
</div>