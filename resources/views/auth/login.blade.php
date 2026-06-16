<head>
    @vite(['resources/css/app.css'])
</head>

<x-guest-layout>

<div class="min-h-screen flex items-center justify-center p-4 animated-bg relative">

        <div class="w-full max-w-4xl rounded-2xl overflow-hidden shadow-2xl flex">

            
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

            
            <div class="flex-1 bg-white flex items-center justify-center p-8 lg:p-12">

                <form method="POST" action="{{ route('login') }}" class="w-full max-w-sm space-y-5">
                    @csrf

                    <div class="mb-4">
                        <h2 class="text-2xl font-bold">Добро пожаловать</h2>
                        <p class="text-gray-500 text-sm">Войдите в аккаунт</p>
                    </div>

                    
                    <x-auth-session-status :status="session('status')" />

                    
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

                        <div class="relative mt-1">
                            <x-text-input id="password"
                                          name="password"
                                          type="password"
                                          class="w-full pr-10"
                                          required />
                            <button type="button" onclick="togglePass('password','eye-login')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <svg id="eye-login" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                        </div>
                        <x-input-error :messages="$errors->get('password')" />
                    </div>

                    
                    <label class="flex items-center gap-2 text-sm text-gray-600">
                        <input type="checkbox" name="remember" class="rounded border-gray-300">
                        Запомнить меня
                    </label>

                    
                    <button type="submit"
                            class="w-full py-2 rounded-xl text-white font-semibold"
                            style="background: linear-gradient(135deg, #4338ca 0%, #5b21b6 100%)">
                        Войти →
                    </button>
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

<script>
function togglePass(fieldId, eyeId) {
    const field = document.getElementById(fieldId);
    const eye = document.getElementById(eyeId);
    const isHidden = field.type === 'password';
    field.type = isHidden ? 'text' : 'password';
    eye.innerHTML = isHidden
        ? '<path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>'
        : '<path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>';
}
</script>
</x-guest-layout>
