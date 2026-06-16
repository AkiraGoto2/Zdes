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
                    Почти готово! <span class="text-lime-300">Подтверди email.</span>
                </h1>
                <p class="text-sm text-indigo-200">
                    Мы отправили 6-значный код на твою почту. Введи его справа чтобы завершить регистрацию.
                </p>
            </div>
            <div class="bg-white/10 rounded-xl p-4 backdrop-blur">
                <p class="text-xs text-indigo-200">Код действителен 15 минут. Если не пришёл — нажми «Отправить ещё раз».</p>
            </div>

            {{-- Декоративные круги --}}
            <div class="absolute -top-16 -right-16 w-64 h-64 rounded-full opacity-10"
                 style="background:radial-gradient(circle,#fff,transparent)"></div>
            <div class="absolute -bottom-20 -left-10 w-72 h-72 rounded-full opacity-10"
                 style="background:radial-gradient(circle,#a78bfa,transparent)"></div>
        </div>

        {{-- Правая панель --}}
        <div class="w-full lg:w-7/12 bg-white p-8 sm:p-10 flex flex-col justify-center">

            <div class="max-w-sm mx-auto w-full">

                <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-5"
                     style="background:linear-gradient(135deg,#4338ca,#5b21b6)">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>

                <h2 class="text-2xl font-bold mb-1">Подтверди email</h2>
                <p class="text-sm text-gray-500 mb-6">Введи 6-значный код из письма</p>

                @if(session('resent'))
                    <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl px-4 py-3 text-sm">
                        {{ session('resent') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-4 bg-red-50 border border-red-200 text-red-600 rounded-xl px-4 py-3 text-sm">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('register.verify.store') }}" class="space-y-5">
                    @csrf

                    {{-- 6 ячеек для кода --}}
                    <div class="flex justify-between gap-2" id="code-inputs">
                        @for($i = 0; $i < 6; $i++)
                            <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]"
                                   class="w-full h-14 text-center text-2xl font-bold border-2 border-gray-200 rounded-xl focus:outline-none focus:border-[#4A40E0] transition"
                                   data-index="{{ $i }}">
                        @endfor
                        <input type="hidden" name="code" id="code-value">
                    </div>

                    <button type="submit"
                            class="w-full py-3 rounded-xl text-white font-semibold text-sm hover:opacity-90 transition"
                            style="background:linear-gradient(135deg,#4338ca,#5b21b6)">
                        Подтвердить →
                    </button>
                </form>

                <form method="POST" action="{{ route('register.resend') }}" class="mt-4 text-center">
                    @csrf
                    <button type="submit" class="text-sm text-gray-400 hover:text-[#4A40E0] transition">
                        Не пришёл код? Отправить ещё раз
                    </button>
                </form>

                <div class="text-center mt-3">
                    <a href="{{ route('register') }}" class="text-xs text-gray-300 hover:text-gray-400 transition">
                        ← Изменить email
                    </a>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
const inputs = document.querySelectorAll('#code-inputs input[data-index]');
const hidden = document.getElementById('code-value');

inputs.forEach((input, i) => {
    input.addEventListener('input', (e) => {
        e.target.value = e.target.value.replace(/\D/g, '');
        if (e.target.value && i < inputs.length - 1) inputs[i + 1].focus();
        syncCode();
    });
    input.addEventListener('keydown', (e) => {
        if (e.key === 'Backspace' && !input.value && i > 0) {
            inputs[i - 1].focus();
            inputs[i - 1].value = '';
            syncCode();
        }
    });
    input.addEventListener('paste', (e) => {
        const paste = e.clipboardData.getData('text').replace(/\D/g, '').slice(0, 6);
        paste.split('').forEach((ch, idx) => { if (inputs[idx]) inputs[idx].value = ch; });
        if (inputs[paste.length - 1]) inputs[paste.length - 1].focus();
        syncCode();
        e.preventDefault();
    });
});

function syncCode() {
    hidden.value = [...inputs].map(i => i.value).join('');
}
</script>

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
