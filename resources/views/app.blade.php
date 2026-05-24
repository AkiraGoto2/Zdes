<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'ГдеДвиж') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-[#F5F5F7] text-[#0c0c0c] font-sans antialiased min-h-screen flex flex-col">

    <header class="w-full border-b border-gray-100 bg-white sticky top-0 z-50" x-data="{ open: false }">
        <div class="max-w-[1280px] mx-auto h-[65px] flex items-center justify-between px-4 sm:px-6">

            {{-- Лого + навигация --}}
            <div class="flex items-center gap-6 sm:gap-8">
                <a href="/" class="block flex-shrink-0">
                    <x-application-logo style="width:90px;height:auto;" />
                </a>
                {{-- Десктоп навигация --}}
                <nav class="hidden md:flex items-center gap-6">
                    <a href="{{ url('/') }}" class="text-[14px] font-medium transition {{ request()->is('/') ? 'text-[#4A40E0] underline underline-offset-4' : 'text-[#475569] hover:text-[#4A40E0]' }}">Главная</a>
                    <a href="{{ url('/events') }}" class="text-[14px] font-medium transition {{ request()->is('events') ? 'text-[#4A40E0] underline underline-offset-4' : 'text-[#475569] hover:text-[#4A40E0]' }}">События</a>
                    @auth
                        <a href="{{ url('/my-events') }}" class="text-[14px] font-medium transition {{ request()->is('my-events') ? 'text-[#4A40E0] underline underline-offset-4' : 'text-[#475569] hover:text-[#4A40E0]' }}">Мои события</a>
                    @endauth
                </nav>
            </div>

            {{-- Десктоп правая часть --}}
            <div class="hidden md:flex items-center gap-3">
                @auth
                    @if(Auth::user()->isAdmin())
                        <a href="{{ route('admin.index') }}" class="text-[13px] font-medium text-amber-600 hover:text-amber-700 flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            Модерация
                            @php $adminPending = \App\Models\Event::where('status','pending')->count(); @endphp
                            @if($adminPending > 0)
                                <span class="bg-amber-500 text-white text-[10px] font-bold rounded-full w-4 h-4 flex items-center justify-center">{{ $adminPending }}</span>
                            @endif
                        </a>
                    @endif
                    @php $unread = Auth::user()->notifications()->where('is_read',false)->count(); @endphp
                    <a href="{{ route('dashboard') }}" class="relative text-[14px] font-medium text-[#475569] hover:text-[#4A40E0] transition">
                        {{ Auth::user()->name }}
                        @if($unread > 0)
                            <span class="absolute -top-1 -right-2 bg-[#4A40E0] text-white text-[9px] font-bold rounded-full w-3.5 h-3.5 flex items-center justify-center">{{ $unread }}</span>
                        @endif
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-[14px] font-medium text-[#475569] hover:text-red-500 transition">Выйти</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="bg-[#4A40E0] text-white rounded-[12px] text-[14px] py-2 px-5 font-semibold hover:bg-[#3d35c7] transition-colors">Войти</a>
                @endauth
            </div>

            {{-- Гамбургер (мобайл) --}}
            <button @click="open = !open" class="md:hidden p-2 rounded-xl text-gray-500 hover:bg-gray-100 transition-colors">
                <svg x-show="!open" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                <svg x-show="open" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Мобильное меню --}}
        <div x-show="open" x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="md:hidden border-t border-gray-100 bg-white px-4 py-3 space-y-1">

            <a href="{{ url('/') }}" @click="open=false" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->is('/') ? 'bg-indigo-50 text-[#4A40E0]' : 'text-gray-700 hover:bg-gray-50' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Главная
            </a>
            <a href="{{ url('/events') }}" @click="open=false" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->is('events') ? 'bg-indigo-50 text-[#4A40E0]' : 'text-gray-700 hover:bg-gray-50' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                События
            </a>
            @auth
                <a href="{{ url('/my-events') }}" @click="open=false" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    Мои события
                </a>
                <a href="{{ route('dashboard') }}" @click="open=false" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    Профиль ({{ Auth::user()->name }})
                </a>
                @if(Auth::user()->isAdmin())
                    <a href="{{ route('admin.index') }}" @click="open=false" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-amber-600 hover:bg-amber-50">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        Модерация
                    </a>
                @endif
                <div class="pt-1 border-t border-gray-100 mt-1">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-red-500 hover:bg-red-50">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            Выйти
                        </button>
                    </form>
                </div>
            @else
                <div class="pt-2 border-t border-gray-100 mt-1 flex gap-2">
                    <a href="{{ route('register') }}" @click="open=false" class="flex-1 text-center border border-gray-200 text-gray-700 rounded-xl py-2.5 text-sm font-semibold hover:bg-gray-50">Регистрация</a>
                    <a href="{{ route('login') }}"    @click="open=false" class="flex-1 text-center bg-[#4A40E0] text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-[#3d35c7]">Войти</a>
                </div>
            @endauth
        </div>
    </header>

    <main class="flex-1">{{ $slot }}</main>

    <footer class="border-t bg-white py-4 text-center text-sm text-gray-500">
        © {{ date('Y') }} ГдеДвиж — все права защищены
    </footer>

    @stack('scripts')
</body>
</html>
