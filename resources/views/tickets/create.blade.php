<x-app-layout>
<div class="max-w-xl mx-auto px-4 py-10">

    <a href="{{ route('events.show', $event) }}"
       class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-[#4A40E0] mb-6 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Назад к событию
    </a>

    
    <div class="bg-indigo-50 border border-indigo-100 rounded-2xl p-4 mb-6 flex gap-4 items-start">
        @if($event->photos->first())
            <img src="{{ Storage::url($event->photos->first()->path) }}"
                 class="w-16 h-16 rounded-xl object-cover flex-shrink-0" alt="">
        @else
            <div class="w-16 h-16 rounded-xl bg-[#4A40E0] flex-shrink-0 flex items-center justify-center">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
        @endif
        <div class="flex-1 min-w-0">
            <p class="font-bold text-sm text-gray-900 leading-snug">{{ $event->name }}</p>
            <p class="text-xs text-gray-500 mt-1">
                {{ \Carbon\Carbon::parse($event->event_date)->translatedFormat('d F Y, H:i') }}
            </p>
            <p class="text-xs text-gray-500">{{ $event->address }}</p>
        </div>
        <div class="text-right flex-shrink-0">
            @if(!$event->price || $event->price == 0)
                <span class="text-sm font-bold text-emerald-600">Бесплатно</span>
            @else
                <span class="text-sm font-bold text-gray-900">от {{ number_format($event->price, 0, '', ' ') }} ₽</span>
            @endif
        </div>
    </div>

    
    <div class="bg-white rounded-2xl border border-gray-200 p-6">
        <h1 class="text-xl font-bold mb-1">Оформление билета</h1>
        <p class="text-sm text-gray-500 mb-6">Билет придёт на указанный email</p>

        <form method="POST" action="{{ route('tickets.store', $event) }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Ваше имя</label>
                <input type="text" name="buyer_name" required
                       value="{{ old('buyer_name', Auth::user()->name . ' ' . Auth::user()->lastname) }}"
                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0]">
                @error('buyer_name')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email для билета</label>
                <input type="email" name="buyer_email" required
                       value="{{ old('buyer_email', Auth::user()->email ?? '') }}"
                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0]">
                @error('buyer_email')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Телефон <span class="text-gray-400">(необязательно)</span></label>
                <input type="tel" name="buyer_phone"
                       value="{{ old('buyer_phone', Auth::user()->tel ?? '') }}"
                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0]">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Количество билетов</label>
                <select name="quantity"
                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0]">
                    @for($i = 1; $i <= 10; $i++)
                        <option value="{{ $i }}" {{ old('quantity', 1) == $i ? 'selected' : '' }}>{{ $i }}</option>
                    @endfor
                </select>
            </div>

            
            @if($event->price && $event->price > 0)
            <div class="bg-gray-50 rounded-xl p-4 flex justify-between items-center">
                <span class="text-sm text-gray-600">Итого</span>
                <span class="font-bold text-lg text-[#4A40E0]" id="total-price">
                    {{ number_format($event->price, 0, '', ' ') }} ₽
                </span>
            </div>
            <script>
                const price = {{ $event->price }};
                document.querySelector('[name=quantity]').addEventListener('change', function() {
                    document.getElementById('total-price').textContent =
                        (price * this.value).toLocaleString('ru-RU') + ' ₽';
                });
            </script>
            @else
            <div class="bg-emerald-50 rounded-xl p-4 flex justify-between items-center">
                <span class="text-sm text-gray-600">Итого</span>
                <span class="font-bold text-lg text-emerald-600">Бесплатно</span>
            </div>
            @endif
<button type="submit"
                    class="w-full bg-[#4A40E0] text-white rounded-xl py-3 text-sm font-semibold hover:bg-[#3d35c7] transition-colors">
                {{ (!$event->price || $event->price == 0) ? 'Получить бесплатный билет' : 'Оформить билет →' }}
            </button>
        </form>
    </div>
</div>
</x-app-layout>
