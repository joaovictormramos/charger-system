<nav class="bg-white border-b border-gray-200 px-4 py-3 flex items-center justify-between sticky top-0 z-10">
    <div class="flex items-center gap-3">
        <img src="{{ asset('images/logo.png') }}" alt="Tenda Energia Solar" class="h-7 object-contain">
        <span class="text-xs text-gray-400 hidden sm:block">Admin</span>
    </div>
    <div class="flex items-center gap-1 overflow-x-auto">
        <a href="{{ route('admin.dashboard') }}"
            class="flex items-center gap-1 px-3 py-2 text-xs rounded-xl font-medium whitespace-nowrap transition-colors
                {{ request()->routeIs('admin.dashboard') ? 'bg-[#FF8400] text-white' : 'text-gray-500 hover:bg-gray-100' }}">
            <i class="ti ti-layout-dashboard text-sm"></i>
            <span class="hidden sm:block">Dashboard</span>
        </a>
        <a href="{{ route('admin.chargers') }}"
            class="flex items-center gap-1 px-3 py-2 text-xs rounded-xl font-medium whitespace-nowrap transition-colors
                {{ request()->routeIs('admin.chargers') ? 'bg-[#FF8400] text-white' : 'text-gray-500 hover:bg-gray-100' }}">
            <i class="ti ti-plug text-sm"></i>
            <span class="hidden sm:block">Carregadores</span>
        </a>
        <a href="{{ route('admin.transactions') }}"
            class="flex items-center gap-1 px-3 py-2 text-xs rounded-xl font-medium whitespace-nowrap transition-colors
                {{ request()->routeIs('admin.transactions') ? 'bg-[#FF8400] text-white' : 'text-gray-500 hover:bg-gray-100' }}">
            <i class="ti ti-receipt text-sm"></i>
            <span class="hidden sm:block">Transações</span>
        </a>
        <a href="{{ route('admin.rfid-cards') }}"
            class="flex items-center gap-1 px-3 py-2 text-xs rounded-xl font-medium whitespace-nowrap transition-colors
                {{ request()->routeIs('admin.rfid-cards') ? 'bg-[#FF8400] text-white' : 'text-gray-500 hover:bg-gray-100' }}">
            <i class="ti ti-credit-card text-sm"></i>
            <span class="hidden sm:block">Cartões</span>
        </a>
    </div>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="text-xs text-gray-400 hover:text-gray-600 flex items-center gap-1 px-2 py-2">
            <i class="ti ti-logout text-sm"></i>
            <span class="hidden sm:block">Sair</span>
        </button>
    </form>
</nav>