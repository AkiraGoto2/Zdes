<x-app-layout>
    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <style>
            #event-map { height: 220px; border-radius: 12px; }
            .leaflet-control-attribution { display: none !important; }
            /* Лайтбокс */
            #lightbox { display:none; position:fixed; inset:0; background:rgba(0,0,0,.9); z-index:9999; align-items:center; justify-content:center; }
            #lightbox.open { display:flex; }
            #lightbox img { max-width:90vw; max-height:90vh; border-radius:12px; object-fit:contain; }
            #lightbox-close { position:absolute; top:20px; right:24px; color:white; font-size:28px; cursor:pointer; line-height:1; }
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
            {{-- Обложка или цветная полоска --}}
            @if($event->photos->first())
                <div class="h-56 overflow-hidden">
                    <img src="{{ Storage::url($event->photos->first()->path) }}"
                         class="w-full h-full object-cover"
                         alt="{{ $event->name }}">
                </div>
            @else
                <div class="h-2 bg-[#4A40E0]"></div>
            @endif
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

                {{-- Галерея (если фото больше одного) --}}
                @if($event->photos->count() > 1)
                    <div class="mb-6">
                        <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-2">Фотографии</h2>
                        <div class="grid grid-cols-3 gap-2">
                            @foreach($event->photos->skip(1) as $photo)
                                <div class="aspect-square rounded-xl overflow-hidden bg-gray-100 cursor-pointer"
                                     onclick="openLightbox('{{ Storage::url($photo->path) }}')">
                                    <img src="{{ Storage::url($photo->path) }}"
                                         class="w-full h-full object-cover hover:scale-105 transition-transform duration-300"
                                         alt="">
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Карта --}}
                @if($event->lat && $event->lng)
                    <div class="mb-6">
                        <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-2">На карте</h2>
                        <div id="event-map"></div>
                    </div>
                @endif

                {{-- Соцсети --}}
                @if($event->socials->count())
                    <div class="mb-4">
                        <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-2">Ссылки</h2>
                        <div class="flex flex-wrap gap-2">
                            @foreach($event->socials as $social)
                                @php
                                    $icons = [
                                        'vk' => '🔵', 'telegram' => '✈️', 'instagram' => '📷',
                                        'youtube' => '▶️', 'tiktok' => '🎵', 'whatsapp' => '💬',
                                    ];
                                    $icon = $icons[mb_strtolower($social->platform)] ?? '🔗';
                                @endphp
                                <a href="{{ $social->url }}" target="_blank" rel="noopener"
                                   class="inline-flex items-center gap-1.5 bg-gray-50 border border-gray-200 rounded-xl px-3 py-1.5 text-sm font-medium text-gray-700 hover:border-[#4A40E0] hover:text-[#4A40E0] transition-colors">
                                    <span>{{ $icon }}</span>
                                    {{ $social->platform }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Организатор --}}
                <div class="border-t pt-4 mt-4">
                    <p class="text-xs text-gray-400">Организатор: <span class="text-gray-600 font-medium">{{ $event->user->name }} {{ $event->user->lastname }}</span></p>
                </div>

            </div>
        </div>

        {{-- Кнопка записи --}}
        @auth
            @if(Auth::id() !== $event->user_id && $event->status === 'approved')
                @php
                    $isApplied = \App\Models\Application::where('user_id', Auth::id())
                        ->where('event_id', $event->id)
                        ->where('status', 'published')
                        ->exists();
                @endphp
                @if($isApplied)
                    <div class="mt-4 flex items-center gap-3">
                        <div class="flex items-center gap-2 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl px-5 py-2.5 text-sm font-medium">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            Вы записаны
                        </div>
                        <form method="POST" action="{{ route('events.unapply', $event) }}">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-sm text-red-500 border border-red-100 rounded-xl px-4 py-2.5 hover:bg-red-50 transition-colors">
                                Отменить запись
                            </button>
                        </form>
                    </div>
                @else
                    <form method="POST" action="{{ route('events.apply', $event) }}" class="mt-4">
                        @csrf
                        <button type="submit"
                            class="w-full bg-[#4A40E0] text-white rounded-xl py-3 text-sm font-semibold hover:bg-[#3d35c7] transition-colors">
                            Записаться на событие
                        </button>
                    </form>
                @endif
            @endif
        @else
            @if($event->status === 'approved')
                <div class="mt-4">
                    <a href="{{ route('login') }}"
                       class="block w-full text-center bg-[#4A40E0] text-white rounded-xl py-3 text-sm font-semibold hover:bg-[#3d35c7] transition-colors">
                        Войдите, чтобы записаться
                    </a>
                </div>
            @endif
        @endauth

        {{-- Flash на странице события --}}
        @if(session('success'))
            <div class="mt-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl px-4 py-2.5 text-sm">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mt-3 bg-red-50 border border-red-200 text-red-600 rounded-xl px-4 py-2.5 text-sm">
                {{ session('error') }}
            </div>
        @endif

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

{{-- Лайтбокс --}}
<div id="lightbox" onclick="closeLightbox()">
    <span id="lightbox-close" onclick="closeLightbox()">×</span>
    <img id="lightbox-img" src="" alt="">
</div>

@push('scripts')
<script>
function openLightbox(src) {
    document.getElementById('lightbox-img').src = src;
    document.getElementById('lightbox').classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeLightbox() {
    document.getElementById('lightbox').classList.remove('open');
    document.body.style.overflow = '';
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeLightbox(); });
</script>
@endpush

</x-app-layout>
