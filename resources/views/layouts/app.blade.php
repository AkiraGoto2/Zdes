<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-[#F5F5F7] text-[#0c0c0c] font-sans antialiased min-h-screen flex flex-col">

    <!-- HEADER -->
    <header class="w-full border-b border-gray-100 bg-white sticky top-0 z-50">
        <div class="max-w-[1280px] mx-auto h-[65px] flex items-center justify-between px-6">

            <div class="flex items-center gap-8">
                <a href="/" class="block">
                    <x-application-logo style="width: 100px; height: auto;" />
                </a>
                <nav class="flex items-center gap-6">
                    <a href="{{ url('/') }}" class="text-[14px] font-medium transition {{ request()->is('/') ? 'text-[#4A40E0] underline underline-offset-4' : 'text-[#475569] hover:text-[#4A40E0]' }}">Главная</a>
                    <a href="{{ url('/events') }}" class="text-[14px] font-medium transition {{ request()->is('events') ? 'text-[#4A40E0] underline underline-offset-4' : 'text-[#475569] hover:text-[#4A40E0]' }}">События</a>
                    @auth
                        <a href="{{ url('/my-events') }}" class="text-[14px] font-medium transition {{ request()->is('my-events') ? 'text-[#4A40E0] underline underline-offset-4' : 'text-[#475569] hover:text-[#4A40E0]' }}">Мои события</a>
                    @endauth
                </nav>
            </div>

            <nav class="flex items-center gap-3">
                @auth
                    <a href="{{ url('/dashboard') }}" class="text-[14px] font-medium text-[#475569] hover:text-[#4A40E0] transition">{{ Auth::user()->name }}</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-[14px] font-medium text-[#475569] hover:text-red-500 transition">Выйти</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="bg-[#4A40E0] text-white rounded-[12px] text-[14px] py-2 px-5 font-semibold hover:bg-[#3d35c7] transition-colors">Войти</a>
                @endauth
            </nav>
        </div>
    </header>

    <!-- CONTENT -->
    <main class="flex-1">
        {{ $slot }}
    </main>

    <!-- FOOTER -->
    <footer class="border-t bg-white py-4 text-center text-sm text-gray-500">
        © {{ date('Y') }} ГдеДвиж — все права защищены
    </footer>

    @stack('scripts')
</body>
</html>
