<?php
use App\Models\Charger;
use App\Models\Transaction;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Barryvdh\DomPDF\Facade\Pdf;

new #[Layout('layouts.admin')] class extends Component
{
    public string $startDate = '';
    public string $endDate = '';
    public string $chargerId = '';

    public function mount(): void
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
    }

    public function getTransactions()
    {
        return Transaction::with(['charger', 'rfidCard'])
            ->whereNotNull('end_time')
            ->when($this->chargerId, fn($q) => $q->where('charger_id', $this->chargerId))
            ->when($this->startDate, fn($q) => $q->whereDate('start_time', '>=', $this->startDate))
            ->when($this->endDate, fn($q) => $q->whereDate('start_time', '<=', $this->endDate))
            ->orderByDesc('start_time')
            ->get();
    }

    public function exportPdf(): \Symfony\Component\HttpFoundation\Response
    {
        $transactions = $this->getTransactions();
        $charger = $this->chargerId ? Charger::find($this->chargerId) : null;

        $pdf = Pdf::loadView('pdf.transactions', [
            'transactions' => $transactions,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'charger' => $charger,
            'totalCost' => $transactions->sum('total_cost'),
            'totalKwh' => $transactions->sum('energy_kwh'),
            'totalSessions' => $transactions->count(),
        ]);

        return response()->streamDownload(
            fn() => print($pdf->output()),
            'transacoes-' . $this->startDate . '-' . $this->endDate . '.pdf'
        );
    }

    public function with(): array
    {
        return [
            'transactions' => $this->getTransactions(),
            'chargers' => Charger::orderBy('identifier')->get(),
            'totalCost' => $this->getTransactions()->sum('total_cost'),
            'totalKwh' => $this->getTransactions()->sum('energy_kwh'),
        ];
    }
};
?>

<div class="min-h-screen bg-gray-50">

    {{-- Header --}}
    <div class="bg-white border-b border-gray-200 px-4 py-4 flex items-center justify-between">
        <h1 class="text-base font-medium text-gray-800">Transações</h1>
        <button wire:click="exportPdf"
            class="flex items-center gap-1 px-3 py-2 bg-[#FF8400] text-white text-sm rounded-xl font-medium">
            <i class="ti ti-file-type-pdf text-base"></i>
            Exportar PDF
        </button>
    </div>

    <div class="p-4">

        {{-- Filtros --}}
        <div class="bg-white rounded-xl border border-gray-200 p-4 mb-4 space-y-3">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Filtros</p>

            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="text-xs text-gray-400 mb-1 block">Data início</label>
                    <input wire:model.live="startDate" type="date"
                        class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-[#FF8400]">
                </div>
                <div>
                    <label class="text-xs text-gray-400 mb-1 block">Data fim</label>
                    <input wire:model.live="endDate" type="date"
                        class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-[#FF8400]">
                </div>
            </div>

            <div>
                <label class="text-xs text-gray-400 mb-1 block">Carregador</label>
                <select wire:model.live="chargerId"
                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-[#FF8400]">
                    <option value="">Todos</option>
                    @foreach($chargers as $charger)
                    <option value="{{ $charger->id }}">{{ $charger->name ?? $charger->identifier }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Resumo --}}
        <div class="grid grid-cols-3 gap-2 mb-4">
            <div class="bg-white rounded-xl border border-gray-200 p-3 text-center">
                <p class="text-xs text-gray-400 mb-1">Sessões</p>
                <p class="text-lg font-medium text-gray-800">{{ $transactions->count() }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-3 text-center">
                <p class="text-xs text-gray-400 mb-1">kWh</p>
                <p class="text-lg font-medium text-gray-800">{{ number_format($totalKwh, 1, ',', '.') }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-3 text-center">
                <p class="text-xs text-gray-400 mb-1">Faturado</p>
                <p class="text-lg font-medium text-gray-800">R$ {{ number_format($totalCost, 0, ',', '.') }}</p>
            </div>
        </div>

        {{-- Lista --}}
        <div class="flex flex-col gap-2">
            @forelse($transactions as $t)
            <div class="bg-white rounded-xl border border-gray-200 px-4 py-3">
                <div class="flex justify-between items-start mb-1">
                    <span class="text-sm font-medium text-gray-800">{{ $t->charger->name ?? $t->charger->identifier }}</span>
                    <span class="text-sm font-medium text-gray-800">R$ {{ number_format($t->total_cost, 2, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-xs text-gray-400">
                    <span>{{ number_format($t->energy_kwh, 2, ',', '.') }} kWh · {{ $t->rfid_card_id ? 'RFID' : 'Pix' }}</span>
                    <span>{{ $t->start_time->format('d/m/Y H:i') }}</span>
                </div>
            </div>
            @empty
            <p class="text-center text-gray-400 text-sm py-8">Nenhuma transação no período.</p>
            @endforelse
        </div>

    </div>
</div>