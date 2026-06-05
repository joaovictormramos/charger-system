<?php
use App\Models\RfidCard;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

new #[Layout('layouts.admin')] class extends Component
{
    use WithPagination;

    public string $uid = '';
    public string $search = '';
    public bool $showForm = false;
    public ?int $selectedCard = null;
    public int $creditAmount = 0;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function toggleForm(): void
    {
        $this->showForm = !$this->showForm;
        $this->reset(['uid']);
    }

    public function save(): void
    {
        $this->validate([
            'uid' => 'required|string|unique:rfid_cards,uuid',
        ]);

        RfidCard::create([
            'uuid' => $this->uid,
            'active' => true,
            'balance' => 0,
        ]);

        $this->toggleForm();
    }

    public function toggleActive(int $id): void
    {
        $card = RfidCard::find($id);
        $card->update(['active' => !$card->active]);
    }

    public function selectCard(int $id): void
    {
        $this->selectedCard = $this->selectedCard === $id ? null : $id;
        $this->creditAmount = 0;
    }

    public function addCredit(): void
    {
        $this->validate([
            'creditAmount' => 'required|integer|min:1',
        ]);

        RfidCard::find($this->selectedCard)->increment('balance', $this->creditAmount);
        $this->selectedCard = null;
        $this->creditAmount = 0;
    }

    public function with(): array
    {
        return [
            'cards' => RfidCard::withCount('transactions')
                ->when($this->search, fn($q) => $q->where('uuid', 'like', '%' . $this->search . '%'))
                ->orderByDesc('id')
                ->paginate(20),
        ];
    }
};
?>

<div class="min-h-screen bg-gray-50">

    {{-- Header --}}
    <div class="bg-white border-b border-gray-200 px-4 py-4 flex items-center justify-between">
        <h1 class="text-base font-medium text-gray-800">Cartões RFID</h1>
        <button wire:click="toggleForm"
            class="flex items-center gap-1 px-3 py-2 bg-[#FF8400] text-white text-sm rounded-xl font-medium">
            <i class="ti ti-plus text-base"></i>
            Novo
        </button>
    </div>

    <div class="p-4 space-y-3">

        {{-- Busca --}}
        <div class="relative">
            <i class="ti ti-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
            <input wire:model.live.debounce.300ms="search"
                type="text"
                placeholder="Buscar por UID..."
                class="w-full pl-9 pr-4 py-2 text-sm bg-white border border-gray-200 rounded-xl focus:outline-none focus:border-[#FF8400]">
        </div>

        {{-- Formulário --}}
        @if($showForm)
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-sm font-medium text-gray-800 mb-4">Novo cartão</p>

            <div class="mb-4">
                <label class="text-xs text-gray-400 mb-1 block">UID do cartão</label>
                <input wire:model="uid"
                    type="text"
                    placeholder="ex: A3F2C8D1"
                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-[#FF8400]">
                <p class="text-xs text-gray-400 mt-1">Código único gravado no chip do cartão físico</p>
                @error('uid')
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
        @forelse($cards as $card)
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-4 py-3 flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-800">•••• •••• {{ strtoupper(substr($card->uuid, -4)) }}</p>
                    <p class="text-xs text-gray-400">{{ $card->uuid }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-sm font-medium text-gray-800">
                        R$ {{ number_format($card->balance, 2, ',', '.') }}
                    </span>
                    <button wire:click="toggleActive({{ $card->id }})"
                        class="text-xs px-2 py-1 rounded-lg font-medium
                            {{ $card->active ? 'bg-green-50 text-green-600' : 'bg-gray-100 text-gray-400' }}">
                        {{ $card->active ? 'Ativo' : 'Inativo' }}
                    </button>
                </div>
            </div>

            <div class="px-4 pb-3 flex items-center justify-between">
                <span class="text-xs text-gray-400">{{ $card->transactions_count }} recargas</span>
                <button wire:click="selectCard({{ $card->id }})"
                    class="text-xs text-[#FF8400] font-medium">
                    {{ $selectedCard === $card->id ? 'Cancelar' : 'Adicionar crédito' }}
                </button>
            </div>

            @if($selectedCard === $card->id)
            <div class="border-t border-gray-100 px-4 py-3 flex gap-2">
                <div class="relative flex-1">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-gray-400">R$</span>
                    <input wire:model="creditAmount"
                        type="number"
                        placeholder="0,00"
                        class="w-full pl-8 pr-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-[#FF8400]">
                </div>
                <button wire:click="addCredit"
                    class="px-4 py-2 bg-[#FF8400] text-white text-sm rounded-xl font-medium">
                    Adicionar
                </button>
            </div>
            @error('creditAmount')
            <p class="text-xs text-red-400 px-4 pb-3">{{ $message }}</p>
            @enderror
            @endif
        </div>
        @empty
        <p class="text-center text-gray-400 text-sm py-8">Nenhum cartão encontrado.</p>
        @endforelse

        {{-- Paginação --}}
        @if($cards->hasPages())
        <div class="bg-white rounded-xl border border-gray-200 px-4 py-3 flex items-center justify-between">
            <button
                wire:click="previousPage"
                @disabled(!$cards->onFirstPage())
                class="text-sm text-gray-600 disabled:text-gray-300 flex items-center gap-1">
                <i class="ti ti-chevron-left"></i> Anterior
            </button>
            <span class="text-xs text-gray-400">
                Página {{ $cards->currentPage() }} de {{ $cards->lastPage() }}
            </span>
            <button
                wire:click="nextPage"
                @disabled(!$cards->hasMorePages())
                class="text-sm text-gray-600 disabled:text-gray-300 flex items-center gap-1">
                Próxima <i class="ti ti-chevron-right"></i>
            </button>
        </div>
        @endif

    </div>
</div>