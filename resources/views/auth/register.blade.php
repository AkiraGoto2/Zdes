<x-guest-layout>
<div class="min-h-screen flex items-center justify-center p-4 animated-bg relative">

        <div class="w-full max-w-4xl rounded-2xl overflow-hidden shadow-2xl flex">

            
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

            
            <div class="flex-1 bg-white flex items-center justify-center p-8">

                <form method="POST" action="{{ route('register') }}" class="w-full max-w-sm space-y-4">
                    @csrf

                    <h2 class="text-2xl font-bold">Создать аккаунт</h2>

                    
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

                    
                    <div>
                        <x-input-label for="tel" value="Телефон"/>
                        
                        <x-tel id="tel" name="tel"
                            class="w-full border-gray-300 rounded-md shadow-sm"
                            type="text" :value="old('tel')" /> 
                        <x-input-error :messages="$errors->get('tel')" class="mt-2" />
                    </div>

                    
                    <div>
                        <x-input-label for="email" value="Email"/>
                        <x-text-input id="email" name="email" type="email" class="w-full" :value="old('email')" required autocomplete="username" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="password" value="Пароль"/>
                        <div class="relative">
                            <x-text-input id="password" name="password" type="password" class="w-full pr-10" required />
                            <button type="button" onclick="togglePass('password','eye-pass')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <svg id="eye-pass" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                        </div>
                        <x-input-error :messages="$errors->get('password')" />
                    </div>

                    <div>
                        <x-input-label for="password_confirmation" value="Подтвердите пароль"/>
                        <div class="relative">
                            <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="w-full pr-10" required />
                            <button type="button" onclick="togglePass('password_confirmation','eye-pass2')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <svg id="eye-pass2" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    
                    <button type="submit"
                        class="w-full py-2 rounded-xl text-white font-semibold"
                        style="background: linear-gradient(135deg, #4338ca 0%, #5b21b6 100%)">
                        Создать аккаунт →
                    </button>
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
