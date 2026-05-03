<x-app-layout>
    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <style>
            #event-map { height: 220px; border-radius: 12px; }
        </style>
    @endpush

    <div class="max-w-2xl mx-auto px-4 py-10">

        {{-- Назад --}}
        <a href="{{ route('events') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-[#4A40E0] mb-6 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Все события
        </a>

        {{-- Статус для владельца --}}
        @auth
            @if(Auth::id() === $event->user_id)
                <div class="mb-4 rounded-xl px-4 py-3 text-sm font-medium flex items-center gap-2
                    {{ $event->status === 'approved' ? 'bg-emerald-50 text-emerald-700' : '' }}
                    {{ $event->status === 'pending' ? 'bg-amber-50 text-amber-700' : '' }}
                    {{ $event->status === 'rejected' ? 'bg-red-50 text-red-700' : '' }}">
                    @if($event->status === 'pending')
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        На проверке у модератора
                    @elseif($event->status === 'approved')
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Одобрено и опубликовано
                    @else
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        Отклонено модератором
                    @endif
                </div>
            @endif
        @endauth

        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="h-2 bg-[#4A40E0]"></div>
            <div class="p-6">

                {{-- Категория + возраст --}}
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-medium text-[#4A40E0] bg-indigo-50 rounded-full px-3 py-1">
                        {{ $event->category->name }}
                    </span>
                    <span class="text-xs text-gray-400 font-medium bg-gray-100 rounded-full px-3 py-1">{{ $event->age }}</span>
                </div>

                {{-- Название --}}
                <h1 class="text-2xl font-bold mb-4">{{ $event->name }}</h1>

                {{-- Мета-блок --}}
                <div class="grid grid-cols-2 gap-3 mb-6">
                    <div class="bg-gray-50 rounded-xl p-3">
                        <p class="text-[11px] text-gray-400 font-medium uppercase tracking-wide mb-1">Дата и время</p>
                        <p class="text-sm font-semibold">{{ \Carbon\Carbon::parse($event->event_date)->translatedFormat('d F Y, H:i') }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-3">
                        <p class="text-[11px] text-gray-400 font-medium uppercase tracking-wide mb-1">Вход</p>
                        @if(!$event->price || $event->price == 0)
                            <p class="text-sm font-semibold text-emerald-600">Бесплатно</p>
                        @elseif($event->price_to)
                            <p class="text-sm font-semibold">{{ number_format($event->price, 0, '', ' ') }} – {{ number_format($event->price_to, 0, '', ' ') }} ₽</p>
                        @else
                            <p class="text-sm font-semibold">{{ number_format($event->price, 0, '', ' ') }} ₽</p>
                        @endif
                    </div>
                    <div class="bg-gray-50 rounded-xl p-3 col-span-2">
                        <p class="text-[11px] text-gray-400 font-medium uppercase tracking-wide mb-1">Адрес</p>
                        <p class="text-sm font-semibold">{{ $event->address }}</p>
                    </div>
                </div>

                {{-- Описание --}}
                <div class="mb-6">
                    <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-2">О событии</h2>
                    <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-line">{{ $event->description }}</p>
                </div>

                {{-- Карта --}}
                @if($event->lat && $event->lng)
                    <div class="mb-6">
                        <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-2">На карте</h2>
                        <div id="event-map"></div>
                    </div>
                @endif

                {{-- Организатор --}}
                <div class="border-t pt-4 mt-4">
                    <p class="text-xs text-gray-400">Организатор: <span class="text-gray-600 font-medium">{{ $event->user->name }} {{ $event->user->lastname }}</span></p>
                </div>

            </div>
        </div>

        {{-- Кнопки управления для владельца --}}
        @auth
            @if(Auth::id() === $event->user_id)
                <div class="mt-4 flex gap-3">
                    <a href="{{ route('events.edit', $event) }}"
                       class="border border-gray-300 text-gray-700 rounded-xl px-5 py-2 text-sm font-medium hover:bg-gray-50 transition-colors">
                        Редактировать
                    </a>
                    <form method="POST" action="{{ route('events.destroy', $event) }}" onsubmit="return confirm('Удалить событие?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="border border-red-200 text-red-500 rounded-xl px-5 py-2 text-sm font-medium hover:bg-red-50 transition-colors">
                            Удалить
                        </button>
                    </form>
                </div>
            @endif
        @endauth

    </div>

    @if($event->lat && $event->lng)
    @push('scripts')
    <script>
        const map = L.map('event-map', {
            center: [{{ $event->lat }}, {{ $event->lng }}],
            zoom: 15,
            zoomControl: true,
            scrollWheelZoom: false
        });
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);
        L.marker([{{ $event->lat }}, {{ $event->lng }}])
            .addTo(map)
            .bindPopup('{{ addslashes($event->name) }}')
            .openPopup();
    </script>
    @endpush
    @endif

</x-app-layout>
