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
                        Город оживает <span class="text-lime-300">прямо сейчас.</span>
                    </h1>
                    <p class="text-sm text-indigo-200">
                        Открой для себя скрытые вечеринки, локальные маркеты и культурные события вашего города.
                    </p>
                </div>

                <div class="bg-white/10 rounded-xl p-4 backdrop-blur">
                    <div class="flex items-center gap-3 mb-3">
                        <img src="/images/user.jpg" class="w-9 h-9 rounded-full object-cover" alt="user">
                        <div>
                            <p class="text-xs font-semibold text-white">Артём Соколов</p>
                            <p class="text-xs text-indigo-200">Нашёл «Виниловый вечер» через ГдеДвиж</p>
                        </div>
                    </div>

                    <p class="text-xs italic text-indigo-100">
                        «Лучший способ узнать, что происходит в городе, не листая бесконечные ленты соцсетей.»
                    </p>
                </div>
            </div>

            {{-- Правая часть --}}
            <div class="flex-1 bg-white flex items-center justify-center p-8 lg:p-12">

                <form method="POST" action="{{ route('login') }}" class="w-full max-w-sm space-y-5">
                    @csrf

                    <div class="mb-4">
                        <h2 class="text-2xl font-bold">Добро пожаловать</h2>
                        <p class="text-gray-500 text-sm">Войдите в аккаунт</p>
                    </div>

                    {{-- Статус --}}
                    <x-auth-session-status :status="session('status')" />

                    {{-- Email --}}
                    <div>
                        <x-input-label for="email" value="Email" />
                        <x-text-input id="email"
                                      name="email"
                                      type="text"
                                      class="w-full mt-1"
                                      value="{{ old('email') }}"
                                      required autofocus />
                        <x-input-error :messages="$errors->get('email')" />
                    </div>

                    {{-- Пароль --}}
                    <div>
                        <div class="flex justify-between">
                            <x-input-label for="password" value="Пароль" />
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}"
                                   class="text-xs text-indigo-600 hover:text-indigo-800">
                                    Забыли пароль?
                                </a>
                            @endif
                        </div>

                        <x-text-input id="password"
                                      name="password"
                                      type="password"
                                      class="w-full mt-1"
                                      required />
                        <x-input-error :messages="$errors->get('password')" />
                    </div>

                    {{-- Запомнить --}}
                    <label class="flex items-center gap-2 text-sm text-gray-600">
                        <input type="checkbox" name="remember" class="rounded border-gray-300">
                        Запомнить меня
                    </label>

                    {{-- Кнопка --}}
                    <button type="submit"
                            class="w-full py-2 rounded-xl text-white font-semibold"
                            style="background: linear-gradient(135deg, #4338ca 0%, #5b21b6 100%)">
                        Войти →
                    </button>

                    {{-- Разделитель --}}
                    <div class="text-center text-xs text-gray-400">или</div>

                    {{-- Соц кнопки (пока заглушка) --}}
                    <div class="grid grid-cols-2 gap-3">
                        <button disabled class="h-10 border rounded-xl text-sm opacity-50">
                            Telegram
                        </button>
                        <button disabled class="h-10 border rounded-xl text-sm opacity-50">
                            VK
                        </button>
                    </div>

                    {{-- Регистрация --}}
                    <p class="text-center text-sm text-gray-500">
                        Нет аккаунта?
                        <a href="{{ route('register') }}" class="text-indigo-600 font-semibold">
                            Зарегистрироваться
                        </a>
                    </p>

                </form>
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