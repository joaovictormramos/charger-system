<?php
use App\Models\Charger;
use Livewire\Component;
use Livewire\Attributes\Layout;

new #[Layout('layouts.app')] class extends Component
{
    public Charger $charger;
    public int $amount = 0;
    public bool $showPix = false;
    public ?int $selectedConnector = null;
    public string $pixCode = '00020126580014br.gov.bcb.pix0136exemplo';

    public function mount(Charger $charger): void
    {
        $this->charger = $charger;
    }

    public function selectConnector(int $connector): void
    {
        $this->selectedConnector = $connector;
        $this->showPix = false;
    }

    public function selectAmount(int $amount): void
    {
        $this->amount = $amount;
        $this->showPix = false;
    }

    public function fee(): int
    {
        return (int) round($this->amount * 0.01);
    }

    public function total(): int
    {
        return $this->amount + $this->fee();
    }

    public function generatePix(): void
    {
        $this->showPix = true;
        // integração com gateway futuramente
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
        <p class="text-sm text-orange-200 mb-1">{{ $charger->identifier }}</p>
        <p class="text-2xl font-medium text-white tracking-tight">Recarga via Pix</p>
    </div>

    <div class="p-4">

        {{-- Seleção de conector --}}
        <div class="mb-6">
            <p class="text-sm text-gray-500 mb-1">Selecione o conector</p>
            <p class="text-xs text-gray-400 mb-3">Verifique o número indicado na estação</p>
            <div class="grid grid-cols-2 gap-2">
                <button wire:click="selectConnector(1)"
                    class="py-4 rounded-xl border text-sm font-medium flex flex-col items-center gap-1 transition-colors
                        {{ $selectedConnector === 1
                            ? 'border-[#FF8400] bg-orange-50 text-orange-700'
                            : 'border-gray-200 text-gray-600' }}">
                    <i class="ti ti-plug text-xl"></i>
                    Conector 1
                    <span class="text-xs font-normal text-green-500">disponível</span>
                </button>
                <button
                    disabled
                    class="py-4 rounded-xl border text-sm font-medium flex flex-col items-center gap-1 border-gray-100 text-gray-300 cursor-not-allowed">
                    <i class="ti ti-plug text-xl"></i>
                    Conector 2
                    <span class="text-xs font-normal text-red-400">em uso</span>
                </button>
            </div>
        </div>

        {{-- Valor --}}
        @if($selectedConnector)
        <div class="mb-4">
            <p class="text-sm text-gray-500 mb-3">Escolha um valor</p>
            <div class="grid grid-cols-2 gap-2 mb-3">
                @foreach([2000, 5000, 10000, 20000] as $value)
                <button wire:click="selectAmount({{ $value }})"
                    class="py-3 rounded-xl text-sm font-medium border transition-colors
                        {{ $amount === $value
                            ? 'border-[#FF8400] bg-orange-50 text-orange-700'
                            : 'border-gray-200 text-gray-700' }}">
                    R$ {{ number_format($value / 100, 2, ',', '.') }}
                </button>
                @endforeach
            </div>
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-400 whitespace-nowrap">Outro valor</span>
                <div class="relative flex-1">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-gray-400">R$</span>
                    <input type="number"
                        wire:model.live="amount"
                        placeholder="0,00"
                        class="w-full pl-8 pr-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-[#FF8400]">
                </div>
            </div>
        </div>

        {{-- Resumo --}}
        @if($amount > 0)
        <div class="border-t border-gray-100 pt-4 mb-4 space-y-2">
            <div class="flex justify-between text-sm">
                <span class="text-gray-400">Recarga</span>
                <span class="text-gray-800">R$ {{ number_format($amount / 100, 2, ',', '.') }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-400">Taxa de plataforma (1%)</span>
                <span class="text-gray-400">R$ {{ number_format($this->fee() / 100, 2, ',', '.') }}</span>
            </div>
            <div class="flex justify-between text-sm font-medium border-t border-gray-100 pt-2">
                <span class="text-gray-800">Total a pagar</span>
                <span class="text-gray-800">R$ {{ number_format($this->total() / 100, 2, ',', '.') }}</span>
            </div>
        </div>

        <button wire:click="generatePix"
            class="w-full py-4 bg-[#FF8400] text-white rounded-xl text-sm font-medium flex items-center justify-center gap-2 mb-4">
            <i class="ti ti-qrcode text-lg"></i>
            Gerar Pix
        </button>
        @endif

        {{-- QR Code --}}
        @if($showPix)
        <div class="bg-gray-50 rounded-xl p-4 text-center">
            <div class="w-32 h-32 bg-white border border-gray-200 rounded-xl flex items-center justify-center mx-auto mb-3">
                <i class="ti ti-qrcode text-7xl text-gray-300"></i>
            </div>
            <p class="text-sm text-gray-400 mb-1">Total a pagar via Pix</p>
            <p class="text-xl font-medium text-gray-800 mb-3">R$ {{ number_format($this->total() / 100, 2, ',', '.') }}</p>
            <div class="flex items-center gap-2 bg-white border border-gray-200 rounded-xl px-3 py-2">
                <span class="text-xs text-gray-400 flex-1 truncate">{{ $pixCode }}</span>
                <button onclick="navigator.clipboard.writeText('{{ $pixCode }}')"
                    class="text-xs text-gray-500 whitespace-nowrap flex items-center gap-1">
                    <i class="ti ti-copy text-sm"></i> Copiar
                </button>
            </div>
            <p class="text-xs text-gray-400 mt-3">Válido por <span class="font-medium text-gray-600">05:00</span></p>
        </div>
        @endif

        @endif

    </div>
</div>