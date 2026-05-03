<x-app-layout>
    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <!-- Flatpickr календарь -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
        <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ru.js"></script>
        <style>
            #pick-map { height: 320px; border-radius: 12px; }
            .flatpickr-input { background: white !important; }
            /* Переключатель платно/бесплатно */
            .price-tab { transition: all .15s; cursor: pointer; }
            .price-tab.active { background: #4A40E0; color: white; }
        </style>
    @endpush

    <div class="max-w-2xl mx-auto px-4 py-10">

        <div class="mb-8">
            <a href="{{ route('my-events') }}" class="inline-flex items-center gap-1 text-sm text-gray-400 hover:text-[#4A40E0] mb-4 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Назад
            </a>
            <h1 class="text-2xl font-bold">Создать событие</h1>
            <p class="text-gray-500 text-sm mt-1">После создания событие отправится на проверку и появится на карте после одобрения.</p>
        </div>

        @if($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('events.store') }}" class="space-y-6">
            @csrf

            {{-- Название --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Название <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required
                    class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0]"
                    placeholder="Например: Виниловый вечер в баре Vinyl">
            </div>

            {{-- Категория + возраст --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Категория <span class="text-red-500">*</span></label>
                    <select name="category_id" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0]">
                        <option value="">Выберите категорию</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Возрастное ограничение <span class="text-red-500">*</span></label>
                    <select name="age" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0]">
                        @foreach(['0+','6+','12+','16+','18+'] as $a)
                            <option value="{{ $a }}" {{ old('age') == $a ? 'selected' : '' }}>{{ $a }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Дата через Flatpickr --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Дата и время <span class="text-red-500">*</span></label>
                <div class="relative">
                    <input type="text" id="event_date_display"
                        class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0] cursor-pointer"
                        placeholder="Выберите дату и время" readonly>
                    <input type="hidden" name="event_date" id="event_date_hidden" value="{{ old('event_date') }}">
                    <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>

            {{-- Цена --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Вход</label>
                <div class="flex gap-2 mb-3">
                    <button type="button" id="tab-free"
                        class="price-tab border border-gray-300 rounded-lg px-4 py-2 text-sm font-medium {{ !old('price') ? 'active' : '' }}"
                        onclick="setPriceType('free')">Бесплатно</button>
                    <button type="button" id="tab-fixed"
                        class="price-tab border border-gray-300 rounded-lg px-4 py-2 text-sm font-medium {{ old('price') && !old('price_to') ? 'active' : '' }}"
                        onclick="setPriceType('fixed')">Фиксированная</button>
                    <button type="button" id="tab-range"
                        class="price-tab border border-gray-300 rounded-lg px-4 py-2 text-sm font-medium {{ old('price_to') ? 'active' : '' }}"
                        onclick="setPriceType('range')">Диапазон</button>
                </div>

                <div id="price-fixed" class="{{ old('price') && !old('price_to') ? '' : 'hidden' }}">
                    <input type="number" name="price" id="price_fixed_input" value="{{ old('price') }}" min="0"
                        class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0]"
                        placeholder="Стоимость в ₽">
                </div>

                <div id="price-range" class="{{ old('price_to') ? '' : 'hidden' }}">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs text-gray-400 mb-1">От ₽</label>
                            <input type="number" name="price" id="price_range_from" value="{{ old('price') }}" min="0"
                                class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0]"
                                placeholder="500">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 mb-1">До ₽</label>
                            <input type="number" name="price_to" value="{{ old('price_to') }}" min="0"
                                class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0]"
                                placeholder="1500">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Описание --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Описание <span class="text-red-500">*</span></label>
                <textarea name="description" rows="4" required minlength="20"
                    class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0] resize-none"
                    placeholder="Расскажите подробнее о событии...">{{ old('description') }}</textarea>
            </div>

            {{-- Адрес + кнопка геокода --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Адрес <span class="text-red-500">*</span></label>
                <div class="flex gap-2">
                    <input type="text" name="address" id="address-input" value="{{ old('address') }}" required
                        class="flex-1 border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0]"
                        placeholder="ул. Ленина, 1, Челябинск">
                    <button type="button" onclick="geocodeAddress()"
                        class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 border border-gray-300 rounded-xl text-sm text-gray-600 transition-colors whitespace-nowrap">
                        Найти на карте
                    </button>
                </div>
            </div>

            {{-- Карта --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Место на карте</label>
                <p class="text-xs text-gray-400 mb-2">Нажмите «Найти на карте» или кликните на карту вручную</p>
                <div id="pick-map"></div>
                <input type="hidden" name="lat" id="lat" value="{{ old('lat') }}">
                <input type="hidden" name="lng" id="lng" value="{{ old('lng') }}">
                <p id="coords-hint" class="text-xs text-gray-400 mt-1">Метка не поставлена</p>
            </div>

            <div class="flex items-center gap-4 pt-2">
                <button type="submit"
                    class="bg-[#4A40E0] text-white rounded-xl px-8 py-2.5 text-sm font-semibold hover:bg-[#3d35c7] transition-colors">
                    Отправить на проверку
                </button>
                <a href="{{ route('my-events') }}" class="text-sm text-gray-500 hover:text-gray-700">Отмена</a>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        // ── Flatpickr ──────────────────────────────────────────
        flatpickr("#event_date_display", {
            locale: "ru",
            enableTime: true,
            dateFormat: "d.m.Y H:i",
            minDate: "today",
            time_24hr: true,
            onChange: function(selectedDates, dateStr) {
                if (selectedDates[0]) {
                    const d = selectedDates[0];
                    const pad = n => String(n).padStart(2, '0');
                    document.getElementById('event_date_hidden').value =
                        `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())} ${pad(d.getHours())}:${pad(d.getMinutes())}:00`;
                }
            }
        });

        @if(old('event_date'))
            document.getElementById('event_date_display')._flatpickr.setDate('{{ old('event_date') }}');
        @endif

        // ── Переключатель цены ─────────────────────────────────
        function setPriceType(type) {
            document.getElementById('price-fixed').classList.add('hidden');
            document.getElementById('price-range').classList.add('hidden');
            ['tab-free','tab-fixed','tab-range'].forEach(id => document.getElementById(id).classList.remove('active'));

            if (type === 'fixed') {
                document.getElementById('price-fixed').classList.remove('hidden');
                document.getElementById('tab-fixed').classList.add('active');
            } else if (type === 'range') {
                document.getElementById('price-range').classList.remove('hidden');
                document.getElementById('tab-range').classList.add('active');
            } else {
                document.getElementById('tab-free').classList.add('active');
                // Сбрасываем значения
                document.querySelectorAll('[name="price"],[name="price_to"]').forEach(el => el.value = '');
            }
        }

        // Устанавливаем начальное состояние
        @if(old('price_to'))
            setPriceType('range');
        @elseif(old('price'))
            setPriceType('fixed');
        @else
            setPriceType('free');
        @endif

        // ── Карта ──────────────────────────────────────────────
        const map = L.map('pick-map', { center: [55.1540, 61.4026], zoom: 12 });
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        let marker = null;

        function setMarker(lat, lng) {
            if (marker) marker.remove();
            marker = L.marker([lat, lng]).addTo(map);
            document.getElementById('lat').value = lat;
            document.getElementById('lng').value = lng;
            document.getElementById('coords-hint').textContent = `Метка: ${parseFloat(lat).toFixed(5)}, ${parseFloat(lng).toFixed(5)}`;
        }

        const oldLat = document.getElementById('lat').value;
        const oldLng = document.getElementById('lng').value;
        if (oldLat && oldLng) {
            setMarker(oldLat, oldLng);
            map.setView([oldLat, oldLng], 15);
        }

        map.on('click', e => setMarker(e.latlng.lat, e.latlng.lng));

        // Геокодирование через Nominatim
        function geocodeAddress() {
            const addr = document.getElementById('address-input').value.trim();
            if (!addr) return;

            document.getElementById('coords-hint').textContent = 'Ищем адрес...';

            fetch(`https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(addr)}&format=json&limit=1`, {
                headers: { 'Accept-Language': 'ru' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.length > 0) {
                    const lat = parseFloat(data[0].lat);
                    const lng = parseFloat(data[0].lon);
                    setMarker(lat, lng);
                    map.setView([lat, lng], 16);
                } else {
                    document.getElementById('coords-hint').textContent = 'Адрес не найден. Укажите метку вручную.';
                }
            })
            .catch(() => {
                document.getElementById('coords-hint').textContent = 'Ошибка геокодирования. Укажите метку вручную.';
            });
        }

        // Enter в поле адреса → геокодируем
        document.getElementById('address-input').addEventListener('keydown', e => {
            if (e.key === 'Enter') { e.preventDefault(); geocodeAddress(); }
        });
    </script>
    @endpush
</x-app-layout>
