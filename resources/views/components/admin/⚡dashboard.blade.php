<?php
use App\Models\Charger;
use App\Models\Transaction;
use Livewire\Component;
use Livewire\Attributes\Layout;

new #[Layout('layouts.app')] class extends Component
{
    public function with(): array
    {
        $chargers = Charger::with(['transactions' => function ($q) {
            $q->whereNotNull('end_time')
              ->orderByDesc('start_time')
              ->limit(5);
        }])->get();

        $monthTransactions = Transaction::whereNotNull('end_time')
            ->whereMonth('start_time', now()->month)
            ->whereYear('start_time', now()->year);

        return [
            'chargers' => $chargers,
            'onlineCount' => $chargers->whereIn('status', ['Available', 'Charging', 'Preparing'])->count(),
            'faultedCount' => $chargers->where('status', 'Faulted')->count(),
            'activeCount' => $chargers->where('status', 'Charging')->count(),
            'monthTotal' => (clone $monthTransactions)->sum('total_cost'),
            'monthKwh' => (clone $monthTransactions)->sum('energy_kwh'),
            'monthSessions' => (clone $monthTransactions)->count(),
            'monthAvgTicket' => (clone $monthTransactions)->count() > 0
                ? (clone $monthTransactions)->sum('total_cost') / (clone $monthTransactions)->count()
                : 0,
        ];
    }
};
?>

<div class="min-h-screen bg-gray-50">

    {{-- Header --}}
    <div class="bg-white border-b border-gray-200 px-4 py-4">
        <h1 class="text-base font-medium text-gray-800">Dashboard</h1>
        <p class="text-xs text-gray-400 mb-3">{{ now()->format('d \d\e F \d\e Y') }}</p>
        <div class="flex gap-2 overflow-x-auto pb-1">
            <a href="{{ route('admin.dashboard') }}"
                class="flex items-center gap-1 px-3 py-2 bg-[#FF8400] text-white text-xs rounded-xl font-medium whitespace-nowrap">
                <i class="ti ti-layout-dashboard"></i> Dashboard
            </a>
            <a href="{{ route('admin.chargers') }}"
                class="flex items-center gap-1 px-3 py-2 bg-gray-100 text-gray-600 text-xs rounded-xl font-medium whitespace-nowrap">
                <i class="ti ti-plug"></i> Carregadores
            </a>
            <a href="{{ route('admin.transactions') }}"
                class="flex items-center gap-1 px-3 py-2 bg-gray-100 text-gray-600 text-xs rounded-xl font-medium whitespace-nowrap">
                <i class="ti ti-receipt"></i> Transações
            </a>
            <a href="{{ route('admin.rfid-cards') }}"
                class="flex items-center gap-1 px-3 py-2 bg-gray-100 text-gray-600 text-xs rounded-xl font-medium whitespace-nowrap">
                <i class="ti ti-credit-card"></i> Cartões
            </a>
        </div>
    </div>

    <div class="p-4 space-y-4">

        {{-- Alertas --}}
        @if($faultedCount > 0)
        <div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 flex items-center gap-3">
            <i class="ti ti-alert-triangle text-red-500 text-xl flex-shrink-0"></i>
            <p class="text-sm text-red-600 font-medium">{{ $faultedCount }} carregador(es) com falha</p>
        </div>
        @endif

        {{-- Status em tempo real --}}
        <div class="grid grid-cols-3 gap-2">
            <div class="bg-white rounded-xl border border-gray-200 p-3 text-center">
                <p class="text-xs text-gray-400 mb-1">Online</p>
                <p class="text-2xl font-medium text-green-500">{{ $onlineCount }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-3 text-center">
                <p class="text-xs text-gray-400 mb-1">Carregando</p>
                <p class="text-2xl font-medium text-blue-500">{{ $activeCount }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-3 text-center">
                <p class="text-xs text-gray-400 mb-1">Com falha</p>
                <p class="text-2xl font-medium text-red-500">{{ $faultedCount }}</p>
            </div>
        </div>

        {{-- Resumo do mês --}}
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-3">
                {{ now()->translatedFormat('F Y') }}
            </p>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <p class="text-xs text-gray-400 mb-1">Faturamento</p>
                    <p class="text-xl font-medium text-gray-800">R$ {{ number_format($monthTotal / 100, 2, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-1">kWh consumidos</p>
                    <p class="text-xl font-medium text-gray-800">{{ number_format($monthKwh, 1, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-1">Sessões</p>
                    <p class="text-xl font-medium text-gray-800">{{ $monthSessions }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-1">Ticket médio</p>
                    <p class="text-xl font-medium text-gray-800">R$ {{ number_format($monthAvgTicket / 100, 2, ',', '.') }}</p>
                </div>
            </div>
        </div>

        {{-- Últimas transações por carregador --}}
        @foreach($chargers as $charger)
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-800">{{ $charger->name ?? $charger->identifier }}</p>
                    @if($charger->name)
                    <p class="text-xs text-gray-400">{{ $charger->identifier }}</p>
                    @endif
                </div>
                <span class="text-xs px-2 py-1 rounded-lg font-medium
                    {{ $charger->status === 'Available' ? 'bg-green-50 text-green-600' : '' }}
                    {{ $charger->status === 'Charging' ? 'bg-blue-50 text-blue-600' : '' }}
                    {{ $charger->status === 'Faulted' ? 'bg-red-50 text-red-500' : '' }}
                    {{ $charger->status === 'Unavailable' ? 'bg-gray-100 text-gray-400' : '' }}
                    {{ $charger->status === 'Preparing' ? 'bg-yellow-50 text-yellow-600' : '' }}">
                    {{ $charger->status }}
                </span>
            </div>

            @forelse($charger->transactions as $t)
            <div class="px-4 py-3 border-b border-gray-50 flex justify-between items-center">
                <div>
                    <p class="text-xs text-gray-800">{{ $t->start_time->format('d/m/Y H:i') }}</p>
                    <p class="text-xs text-gray-400">{{ number_format($t->energy_kwh, 2, ',', '.') }} kWh · {{ $t->rfid_card_id ? 'RFID' : 'Pix' }}</p>
                </div>
                <span class="text-sm font-medium text-gray-800">R$ {{ number_format($t->total_cost / 100, 2, ',', '.') }}</span>
            </div>
            @empty
            <div class="px-4 py-3">
                <p class="text-xs text-gray-400">Nenhuma transação ainda.</p>
            </div>
            @endforelse
        </div>
        @endforeach

    </div>
</div>