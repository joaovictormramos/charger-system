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

    public function getListeners(): array
    {
        return [
            'echo:session.' . $this->transaction->uuid . ',SessionUpdated' => 'refreshSession',
        ];
    }

    public function refreshSession(): void
    {
        $this->transaction->refresh();

        if ($this->transaction->end_time) {
            $this->redirect(route('receipt.show', $this->transaction));
        }
    }

    public function stopSession(): void
    {
        $this->redirect(route('receipt.show', $this->transaction));
    }

    public function with(): array
    {
        return [
            'energyKwh' => number_format($this->transaction->energy_kwh ?? 0, 2, ',', '.'),
            'totalCost' => number_format(($this->transaction->total_cost ?? 0) / 100, 2, ',', '.'),
            'paidAmount' => number_format(($this->transaction->paid_amount ?? 0) / 100, 2, ',', '.'),
        ];
    }
    
};
?>

<div class="min-h-screen bg-white">

    {{-- Header --}}
    <div class="bg-[#FF8400] px-4 pt-5 pb-6">
        <div class="flex items-center justify-between mb-5">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-7 object-contain brightness-0 invert">
            <div class="flex items-center gap-2 text-orange-100 text-xs">
                <span class="w-2 h-2 rounded-full bg-white animate-pulse inline-block"></span>
                Carregando
            </div>
        </div>
        <p class="text-sm text-orange-200 mb-1">{{ $transaction->charger->identifier }}</p>
        <p class="text-2xl font-medium text-white">Sessão ativa</p>
    </div>

    <div class="p-4">

        {{-- Métricas --}}
        <div class="grid grid-cols-2 gap-3 mb-6">
            <div class="bg-gray-50 rounded-xl p-4 text-center">
                <p class="text-xs text-gray-400 mb-1">Energia carregada</p>
                <p class="text-2xl font-medium text-gray-800">{{ $energyKwh }}</p>
                <p class="text-xs text-gray-400 mt-1">kWh</p>
            </div>
            <div class="bg-gray-50 rounded-xl p-4 text-center">
                <p class="text-xs text-gray-400 mb-1">Tempo decorrido</p>
                <p class="text-2xl font-medium text-gray-800" id="timer">00:00</p>
                <p class="text-xs text-gray-400 mt-1">min</p>
            </div>
            <div class="bg-gray-50 rounded-xl p-4 text-center">
                <p class="text-xs text-gray-400 mb-1">Valor consumido</p>
                <p class="text-2xl font-medium text-gray-800">R$ {{ $totalCost }}</p>
                <p class="text-xs text-gray-400 mt-1">de R$ {{ $paidAmount }}</p>
            </div>
            <div class="bg-gray-50 rounded-xl p-4 text-center">
                <p class="text-xs text-gray-400 mb-1">Potência atual</p>
                <p class="text-2xl font-medium text-[#FF8400]">{{ $transaction->charger->power_kw ?? '—' }}</p>
                <p class="text-xs text-gray-400 mt-1">kW</p>
            </div>
        </div>

        {{-- Barra de progresso --}}
        <div class="mb-6">
            <div class="flex justify-between text-xs text-gray-400 mb-2">
                <span>Progresso do crédito</span>
                <span id="pct">0%</span>
            </div>
            <div class="bg-gray-100 rounded-full h-2 overflow-hidden">
                <div id="progressbar" class="h-full bg-[#FF8400] rounded-full transition-all duration-1000" style="width: 0%"></div>
            </div>
        </div>

        {{-- Info --}}
        <div class="bg-gray-50 rounded-xl px-4 py-3 mb-4">
            <div class="flex justify-between text-sm">
                <span class="text-gray-400">Preço por kWh</span>
                <span class="text-gray-800">R$ {{ number_format($transaction->charger->price_per_kwh / 100, 2, ',', '.') }}</span>
            </div>
        </div>

        <button onclick="document.getElementById('dialog-stop').showModal()"
            class="w-full py-4 border border-red-200 text-red-500 rounded-xl text-sm font-medium flex items-center justify-center gap-2 mb-4">
            <i class="ti ti-plug-x text-lg"></i>
            Encerrar recarga
        </button>

        <dialog id="dialog-stop" class="rounded-2xl shadow-xl p-0" style="position: fixed; margin: auto; inset: 0; width: min(85vw, 320px); height: fit-content;">
            <div class="p-6">
                <p class="text-base font-medium text-gray-800 mb-1">Encerrar recarga?</p>

                @if($transaction->rfid_card_id)
                    <p class="text-sm text-gray-400 mb-6">Você será cobrado apenas pelo que consumiu até agora.</p>
                @else
                    <p class="text-sm text-gray-400 mb-6">O valor não utilizado será estornado automaticamente para o Pix de origem.</p>
                @endif

                <div class="flex gap-2">
                    <button onclick="document.getElementById('dialog-stop').close()"
                        class="flex-1 py-3 rounded-xl border border-gray-200 text-sm text-gray-600">
                        Cancelar
                    </button>
                    <button wire:click="stopSession"
                        onclick="document.getElementById('dialog-stop').close()"
                        class="flex-1 py-3 rounded-xl bg-red-500 text-white text-sm font-medium">
                        Encerrar
                    </button>
                </div>
            </div>
        </dialog>

        <p class="text-xs text-gray-400 text-center">A sessão encerra automaticamente quando o crédito for consumido ou o cabo for desconectado.</p>

    </div>
    <div class="min-h-screen bg-white"
    data-start="{{ $transaction->start_time->toIso8601String() }}"
    data-paid="{{ $transaction->paid_amount ?? 0 }}"
    data-price="{{ $transaction->charger->price_per_kwh }}"
    id="session-root">
</div>

<script>
    const root = document.getElementById('session-root');
    const start = new Date(root.dataset.start);
    const paidCents = parseInt(root.dataset.paid);
    const pricePerKwh = parseInt(root.dataset.price);

    setInterval(() => {
        const diff = Math.floor((new Date() - start) / 1000);
        const m = String(Math.floor(diff / 60)).padStart(2, '0');
        const s = String(diff % 60).padStart(2, '0');
        document.getElementById('timer').textContent = m + ':' + s;
    }, 1000);
</script>