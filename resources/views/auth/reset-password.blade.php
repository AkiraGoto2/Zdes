<head>
    @vite(['resources/css/app.css'])
</head>

<x-guest-layout>
<div class="min-h-screen flex items-center justify-center p-4 animated-bg relative">
    <div class="w-full max-w-4xl rounded-2xl overflow-hidden shadow-2xl flex">

        {{-- Левая панель --}}
        <div class="hidden lg:flex lg:w-5/12 flex-col justify-between p-10 text-white relative overflow-hidden"
             style="background: linear-gradient(145deg, #3730d4 0%, #4f46e5 60%, #6d28d9 100%);">
            <div>
                <span class="text-xl font-bold">ГдеДвиж</span>
            </div>
            <div class="space-y-4">
                <h1 class="text-3xl font-black">
                    Новый пароль — <span class="text-lime-300">новый старт.</span>
                </h1>
                <p class="text-sm text-indigo-200">
                    Придумай надёжный пароль. Минимум 8 символов — лучше с цифрами и заглавными буквами.
                </p>
            </div>
            <div class="bg-white/10 rounded-xl p-4 backdrop-blur">
                <p class="text-xs text-indigo-200">После смены пароля тебя автоматически перенаправит на страницу входа.</p>
            </div>
            <div class="absolute -top-16 -right-16 w-64 h-64 rounded-full opacity-10"
                 style="background:radial-gradient(circle,#fff,transparent)"></div>
            <div class="absolute -bottom-20 -left-10 w-72 h-72 rounded-full opacity-10"
                 style="background:radial-gradient(circle,#a78bfa,transparent)"></div>
        </div>

        {{-- Правая панель --}}
        <div class="flex-1 bg-white flex items-center justify-center p-8 lg:p-12">
            <div class="w-full max-w-sm space-y-5">

                <div class="mb-4">
                    <h2 class="text-2xl font-bold">Новый пароль</h2>
                    <p class="text-sm text-gray-500 mt-1">Введи новый пароль для аккаунта</p>
                </div>

                @if ($errors->any())
                    <div class="bg-red-50 border border-red-200 text-red-600 rounded-xl px-4 py-3 text-sm">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="token" value="{{ $request->route('token') }}">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus
                            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0]"
                            placeholder="example@mail.ru">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Новый пароль</label>
                        <input type="password" name="password" required
                            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0]"
                            placeholder="Минимум 8 символов">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Повтори пароль</label>
                        <input type="password" name="password_confirmation" required
                            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0]"
                            placeholder="Повтори пароль">
                    </div>

                    <button type="submit"
                            class="w-full py-3 rounded-xl text-white font-semibold text-sm hover:opacity-90 transition"
                            style="background:linear-gradient(135deg,#4338ca,#5b21b6)">
                        Сохранить новый пароль →
                    </button>
                </form>

            </div>
        </div>
    </div>
</div>

<style>
    .animated-bg {
        background-image: url('/images/bg.jpg');
        background-size: 120%;
        background-position: 0% 50%;
        animation: moveBg 40s ease-in-out infinite alternate;
    }
    @keyframes moveBg {
        0% { background-position: 0% 0%; }
        100% { background-position: 100% 100%; }
    }
</style>
</x-guest-layout>
