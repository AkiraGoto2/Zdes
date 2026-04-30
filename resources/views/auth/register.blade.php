<x-guest-layout>
<div class="min-h-screen flex items-center justify-center p-4 animated-bg relative">

        <div class="w-full max-w-4xl rounded-2xl overflow-hidden shadow-2xl flex">

            {{-- Левая панель --}}
            <div class="hidden lg:flex lg:w-5/12 flex-col justify-between p-10 text-white relative overflow-hidden"
                style="background: linear-gradient(145deg, #3730d4 0%, #4f46e5 60%, #6d28d9 100%);">

                <div class="relative z-10">
                    <span class="text-xl font-bold">ГдеДвиж</span>
                </div>

                <div class="relative z-10 space-y-5">
                    <h1 class="text-3xl font-black">
                        Присоединяйся к <span class="text-lime-300">движу города</span>
                    </h1>

                    <ul class="space-y-4">
                        <li class="flex items-center gap-3">
                            <img src="/images/sp.svg" alt="" class="w-6 h-6" />
                            <span class="text-sm">События рядом с вами</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <img src="/images/sp.svg" alt="" class="w-6 h-6" />
                            <span class="text-sm">Flash-события</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <img src="/images/sp.svg" alt="" class="w-6 h-6" />
                            <span class="text-sm">Создай свое мероприятие</span>
                        </li>
                        </ul>
                </div>

                <div class="text-xs opacity-80">
                    Уже 12 400+ пользователей
                </div>
            </div>

            {{-- Правая часть (форма) --}}
            <div class="flex-1 bg-white flex items-center justify-center p-8">

                <form method="POST" action="{{ route('register') }}" class="w-full max-w-sm space-y-4">
                    @csrf

                    <h2 class="text-2xl font-bold">Создать аккаунт</h2>

                    {{-- Имя + фамилия --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <x-input-label for="name" value="Имя"/>
                            <x-text-input id="name" name="name" class="w-full" :value="old('name')" required autofocus autocomplete="name" />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="lastname" value="Фамилия"/>
                            <x-text-input id="lastname" name="lastname" class="w-full" :value="old('lastname')" autocomplete="family-name" />
                            <x-input-error :messages="$errors->get('lastname')" class="mt-2" />
                        </div>
                    </div>

                    {{-- Телефон --}}
                    <div>
                        <x-input-label for="tel" value="Телефон"/>
                        {{-- Исправлено: добавлен закрывающий слэш --}}
                        <x-tel id="tel" name="tel"
                            class="w-full border-gray-300 rounded-md shadow-sm"
                            type="text" :value="old('tel')" /> 
                        <x-input-error :messages="$errors->get('tel')" class="mt-2" />
                    </div>

                    {{-- Email --}}
                    <div>
                        <x-input-label for="email" value="Email"/>
                        <x-text-input id="email" name="email" type="email" class="w-full" :value="old('email')" required autocomplete="username" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    {{-- Пароль --}}
                    <div>
                        <x-input-label for="password" value="Пароль"/>
                        <x-text-input id="password" name="password" type="password" class="w-full" required />
                        <x-input-error :messages="$errors->get('password')" />
                    </div>

                    {{-- Подтверждение --}}
                    <div>
                        <x-input-label for="password_confirmation" value="Подтвердите пароль"/>
                        <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="w-full" required />
                    </div>

                    {{-- Кнопка --}}
                    <button type="submit"
                        class="w-full py-2 rounded-xl text-white font-semibold"
                        style="background: linear-gradient(135deg, #4338ca 0%, #5b21b6 100%)">
                        Создать аккаунт →
                    </button>

                    {{-- Логин --}}
                    <p class="text-sm text-center">
                        Уже есть аккаунт?
                        <a href="{{ route('login') }}" class="text-indigo-600 font-semibold">
                            Войти
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