<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login · {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center px-4">

    <div class="w-full max-w-sm">

        <div class="text-center mb-8">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-10 object-contain mx-auto mb-2">
            <p class="text-sm text-gray-400">Painel administrativo</p>
        </div>

        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">

            <div class="bg-[#FF8400] px-6 py-5">
                <p class="text-white font-medium text-lg">Entrar</p>
                <p class="text-orange-100 text-sm">Acesse o painel de controle</p>
            </div>

            <form method="POST" action="{{ route('login') }}" class="p-6 space-y-4">
                @csrf

                @if (session('status'))
                    <div class="bg-green-50 text-green-600 text-sm px-4 py-3 rounded-xl">
                        {{ session('status') }}
                    </div>
                @endif

                <div>
                    <label for="email" class="text-xs text-gray-400 mb-1 block">E-mail</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}"
                        required autofocus autocomplete="username"
                        class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-[#FF8400]">
                    @error('email')
                        <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="text-xs text-gray-400 mb-1 block">Senha</label>
                    <input id="password" type="password" name="password"
                        required autocomplete="current-password"
                        class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-[#FF8400]">
                    @error('password')
                        <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 text-sm text-gray-500 cursor-pointer">
                        <input type="checkbox" name="remember" class="rounded border-gray-300 text-[#FF8400]">
                        Lembrar de mim
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}"
                            class="text-xs text-[#FF8400] hover:underline">
                            Esqueci a senha
                        </a>
                    @endif
                </div>

                <button type="submit"
                    class="w-full py-3 bg-[#FF8400] text-white text-sm font-medium rounded-xl flex items-center justify-center gap-2">
                    <i class="ti ti-login text-base"></i>
                    Entrar
                </button>

            </form>
        </div>

    </div>

</body>
</html>