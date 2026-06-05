<?php
use App\Models\Charger;
use Livewire\Component;
use Livewire\Attributes\Layout;

new #[Layout('layouts.admin')] class extends Component
{
    public string $identifier = '';
    public string $name = '';
    public string $location = '';
    public int $price_per_kwh = 99;
    public bool $showForm = false;

    public function toggleForm(): void
    {
        $this->showForm = !$this->showForm;
        $this->reset(['identifier', 'price_per_kwh']);
    }

    public function save(): void
    {
        $this->validate([
            'identifier' => 'required|string|unique:chargers,identifier',
            'name' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:100',
            'price_per_kwh' => 'required|integer|min:1',
        ]);

        Charger::create([
            'identifier' => $this->identifier,
            'name' => $this->name,
            'location' => $this->location,
            'price_per_kwh' => $this->price_per_kwh,
            'status' => 'Unavailable',
        ]);

        $this->toggleForm();
    }

    public function with(): array
    {
        return [
            'chargers' => Charger::orderBy('identifier')->get(),
        ];
    }
};
?>

<div class="min-h-screen bg-gray-50">

    {{-- Header --}}
    <div class="bg-white border-b border-gray-200 px-4 py-4 flex items-center justify-between">
        <h1 class="text-base font-medium text-gray-800">Carregadores</h1>
        <button wire:click="toggleForm"
            class="flex items-center gap-1 px-3 py-2 bg-[#FF8400] text-white text-sm rounded-xl font-medium">
            <i class="ti ti-plus text-base"></i>
            Novo
        </button>
    </div>

    <div class="p-4">

        {{-- Formulário --}}
        @if($showForm)
        <div class="bg-white rounded-xl border border-gray-200 p-4 mb-4">
            <p class="text-sm font-medium text-gray-800 mb-4">Novo carregador</p>

            <div class="mb-3">
                <label class="text-xs text-gray-400 mb-1 block">Identificador</label>
                <input wire:model="identifier"
                    type="text"
                    placeholder="ex: TENDA-60K-1"
                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-[#FF8400]">
                @error('identifier')
                    <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-3">
                <label class="text-xs text-gray-400 mb-1 block">Nome amigável</label>
                <input wire:model="name"
                    type="text"
                    placeholder="ex: Estação Tenda 60K"
                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-[#FF8400]">
                @error('name')
                    <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-3">
                <label class="text-xs text-gray-400 mb-1 block">Localização</label>
                <input wire:model="location"
                    type="text"
                    placeholder="ex: Estacionamento B"
                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-[#FF8400]">
                @error('location')
                    <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="text-xs text-gray-400 mb-1 block">Preço por kWh (centavos)</label>
                <input wire:model="price_per_kwh"
                    type="number"
                    placeholder="99"
                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-[#FF8400]">
                <p class="text-xs text-gray-400 mt-1">R$ {{ number_format($price_per_kwh, 2, ',', '.') }} por kWh</p>
                @error('price_per_kwh')
                    <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-2">
                <button wire:click="toggleForm"
                    class="flex-1 py-3 border border-gray-200 text-gray-600 text-sm rounded-xl">
                    Cancelar
                </button>
                <button wire:click="save"
                    class="flex-1 py-3 bg-[#FF8400] text-white text-sm rounded-xl font-medium">
                    Salvar
                </button>
            </div>
        </div>
        @endif

        {{-- Lista --}}
        <div class="flex flex-col gap-3">
            @forelse($chargers as $charger)
            <div class="bg-white rounded-xl border border-gray-200 px-4 py-3">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-800">
                        {{ $charger->name ?? $charger->identifier }}
                    </span>

                    <span class="text-xs text-gray-400">{{ $charger->identifier }}</span>
                    
                    <span class="text-xs px-2 py-1 rounded-lg font-medium
                        {{ $charger->status === 'Available' ? 'bg-green-50 text-green-600' : '' }}
                        {{ $charger->status === 'Charging' ? 'bg-blue-50 text-blue-600' : '' }}
                        {{ $charger->status === 'Faulted' ? 'bg-red-50 text-red-500' : '' }}
                        {{ $charger->status === 'Unavailable' ? 'bg-gray-100 text-gray-400' : '' }}
                        {{ $charger->status === 'Preparing' ? 'bg-yellow-50 text-yellow-600' : '' }}">
                        {{ $charger->status }}
                    </span>
                </div>
                <div class="flex justify-between text-xs text-gray-400">
                    <span>R$ {{ number_format($charger->price_per_kwh, 2, ',', '.') }}/kWh</span>
                    <span>
                        {{ $charger->last_heartbeat ? 'Online ' . $charger->last_heartbeat->diffForHumans() : 'Nunca conectado' }}
                    </span>
                </div>
            </div>
            @empty
            <p class="text-center text-gray-400 text-sm py-8">Nenhum carregador cadastrado.</p>
            @endforelse
        </div>

    </div>
</div>