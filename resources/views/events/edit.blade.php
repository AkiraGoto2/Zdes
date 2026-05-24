<x-app-layout>
    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
        <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ru.js"></script>
        <style>
            #pick-map { height: 300px; border-radius: 12px; }
            .price-tab { transition: all .15s; cursor: pointer; }
            .price-tab.active { background: #4A40E0; color: white; border-color: #4A40E0; }
            #suggestions {
                position: absolute; top: 100%; left: 0; right: 0; z-index: 9999;
                background: white; border: 1px solid #e5e7eb; border-radius: 12px;
                box-shadow: 0 8px 24px rgba(0,0,0,.1); margin-top: 4px;
                max-height: 220px; overflow-y: auto;
            }
            .sug-item {
                padding: 10px 14px; cursor: pointer; font-size: 13px;
                display: flex; align-items: flex-start; gap: 8px;
                border-bottom: 1px solid #f3f4f6;
            }
            .sug-item:last-child { border-bottom: none; }
            .sug-item:hover { background: #f5f3ff; }
            .sug-main { font-weight: 500; color: #111; }
            .sug-sub  { font-size: 11px; color: #9ca3af; margin-top: 1px; }
        </style>
    @endpush

    <div class="max-w-2xl mx-auto px-4 py-10">
        <div class="mb-8">
            <a href="{{ route('my-events') }}" class="inline-flex items-center gap-1 text-sm text-gray-400 hover:text-[#4A40E0] mb-4 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Назад
            </a>
            <h1 class="text-2xl font-bold">Редактировать событие</h1>
            <p class="text-gray-500 text-sm mt-1">После сохранения событие снова отправится на проверку модератору.</p>
        </div>

        @if($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('events.update', $event) }}" enctype="multipart/form-data" class="space-y-6">
            @csrf @method('PATCH')

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Название <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $event->name) }}" required
                    class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0]">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Категория <span class="text-red-500">*</span></label>
                    <select name="category_id" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0]">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $event->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Возрастное ограничение <span class="text-red-500">*</span></label>
                    <select name="age" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0]">
                        @foreach(['0+','6+','12+','16+','18+'] as $a)
                            <option value="{{ $a }}" {{ old('age', $event->age) === $a ? 'selected' : '' }}>{{ $a }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Дата и время <span class="text-red-500">*</span></label>
                <div class="relative">
                    <input type="text" id="event_date_display" readonly
                        class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0] cursor-pointer">
                    <input type="hidden" name="event_date" id="event_date_hidden"
                        value="{{ old('event_date', \Carbon\Carbon::parse($event->event_date)->format('Y-m-d H:i:s')) }}">
                    <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Вход</label>
                <div class="flex gap-2 mb-3">
                    <button type="button" id="tab-free"  class="price-tab border border-gray-300 rounded-lg px-4 py-2 text-sm font-medium" onclick="setPriceType('free')">Бесплатно</button>
                    <button type="button" id="tab-fixed" class="price-tab border border-gray-300 rounded-lg px-4 py-2 text-sm font-medium" onclick="setPriceType('fixed')">Фиксированная</button>
                    <button type="button" id="tab-range" class="price-tab border border-gray-300 rounded-lg px-4 py-2 text-sm font-medium" onclick="setPriceType('range')">Диапазон</button>
                </div>
                <div id="price-fixed" class="hidden">
                    <input type="number" name="price" value="{{ old('price', ($event->price && !$event->price_to) ? $event->price : '') }}" min="0"
                        class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0]" placeholder="Стоимость в ₽">
                </div>
                <div id="price-range" class="hidden">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs text-gray-400 mb-1">От ₽</label>
                            <input type="number" name="price" value="{{ old('price', $event->price_to ? $event->price : '') }}" min="0"
                                class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0]" placeholder="500">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 mb-1">До ₽</label>
                            <input type="number" name="price_to" value="{{ old('price_to', $event->price_to) }}" min="0"
                                class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0]" placeholder="1500">
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Описание <span class="text-red-500">*</span></label>
                <textarea name="description" rows="4" required minlength="20"
                    class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0] resize-none">{{ old('description', $event->description) }}</textarea>
            </div>

            {{-- Адрес с автодополнением --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Адрес <span class="text-red-500">*</span>
                    <span class="text-xs font-normal text-gray-400 ml-1">— начните вводить, выберите из подсказок</span>
                </label>
                <div class="relative">
                    <input type="text" name="address" id="address-input"
                        value="{{ old('address', $event->address) }}"
                        required autocomplete="off"
                        class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0]">
                    <div id="suggestions" class="hidden"></div>
                </div>
            </div>

            {{-- Карта --}}
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="text-sm font-medium text-gray-700">Место на карте</label>
                    <span class="text-xs text-gray-400">Выберите адрес из подсказок или кликните на карту</span>
                </div>

                <div id="pick-map" class="mb-3 border border-gray-200"></div>

                <div id="map-status" class="flex items-center gap-2 text-xs mb-3 {{ ($event->lat && $event->lng) ? 'text-emerald-600' : 'text-gray-400' }}">
                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span id="map-status-text">{{ ($event->lat && $event->lng) ? $event->address : 'Метка не поставлена' }}</span>
                </div>

                <div class="bg-gray-50 rounded-xl p-3 border border-gray-200">
                    <label class="flex items-start gap-2.5 cursor-pointer">
                        <input type="checkbox" id="address-override" name="address_override" value="1"
                            {{ old('address_override', $event->display_address ? '1' : '') ? 'checked' : '' }}
                            class="mt-0.5 w-4 h-4 rounded border-gray-300 text-[#4A40E0] focus:ring-[#4A40E0]">
                        <div>
                            <span class="text-sm font-medium text-gray-700">Текстовый адрес отличается от метки на карте</span>
                            <p class="text-xs text-gray-400 mt-0.5">Метка показывает точное место, а адрес — пояснение</p>
                        </div>
                    </label>
                    <div id="display-address-block" class="{{ old('address_override', $event->display_address) ? '' : 'hidden' }} mt-3">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Отображаемый адрес</label>
                        <input type="text" name="display_address" value="{{ old('display_address', $event->display_address) }}"
                            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0]"
                            placeholder="Например: Вход с ул. Ленина, у арки">
                    </div>
                </div>

                <input type="hidden" name="lat" id="lat" value="{{ old('lat', $event->lat) }}">
                <input type="hidden" name="lng" id="lng" value="{{ old('lng', $event->lng) }}">
            </div>

            {{-- Соцсети --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Ссылки на соцсети</label>
                <div id="socials-list" class="space-y-2">
                    @forelse($event->socials as $i => $social)
                        <div class="flex gap-2 social-row">
                            <select name="socials[{{ $i }}][platform]" class="w-36 border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0]">
                                @foreach(['VK','Telegram','Instagram','YouTube','TikTok','WhatsApp','Сайт'] as $p)
                                    <option value="{{ $p }}" {{ $social->platform === $p ? 'selected' : '' }}>{{ $p }}</option>
                                @endforeach
                            </select>
                            <input type="url" name="socials[{{ $i }}][url]" value="{{ $social->url }}"
                                class="flex-1 border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0]">
                            <button type="button" onclick="this.closest('.social-row').remove()" class="text-gray-400 hover:text-red-500 px-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    @empty
                        <div class="flex gap-2 social-row">
                            <select name="socials[0][platform]" class="w-36 border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0]">
                                <option value="">Платформа</option>
                                @foreach(['VK','Telegram','Instagram','YouTube','TikTok','WhatsApp','Сайт'] as $p)
                                    <option value="{{ $p }}">{{ $p }}</option>
                                @endforeach
                            </select>
                            <input type="url" name="socials[0][url]" placeholder="https://..." class="flex-1 border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0]">
                        </div>
                    @endforelse
                </div>
                <button type="button" onclick="addSocial()" class="mt-2 text-sm text-[#4A40E0] hover:underline flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Добавить ссылку
                </button>
            </div>

            {{-- Управление фотографиями --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Фотографии</label>

                {{-- Существующие фото --}}
                @if($event->photos->count())
                    <div class="grid grid-cols-4 gap-2 mb-3">
                        @foreach($event->photos as $photo)
                            <div class="relative rounded-xl overflow-hidden aspect-square bg-gray-100 group">
                                <img src="{{ Storage::url($photo->path) }}" class="w-full h-full object-cover">
                                @if($loop->first)
                                    <span style="position:absolute;bottom:4px;left:4px;background:rgba(74,64,224,.85);color:white;font-size:10px;font-weight:600;border-radius:5px;padding:1px 6px;">Обложка</span>
                                @endif
                                <form method="POST" action="{{ route('photos.destroy', $photo) }}"
                                    style="position:absolute;top:4px;right:4px;"
                                    onsubmit="return confirm('Удалить фото?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                        class="w-6 h-6 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Загрузить новые --}}
                @if($event->photos->count() < 8)
                    <div id="photo-drop"
                        class="border-2 border-dashed border-gray-300 rounded-xl p-5 text-center cursor-pointer hover:border-[#4A40E0] hover:bg-indigo-50/30 transition-colors"
                        onclick="document.getElementById('photos-input').click()">
                        <p class="text-sm text-gray-500">+ Добавить фото</p>
                        <p class="text-xs text-gray-400 mt-0.5">до {{ 8 - $event->photos->count() }} штук осталось</p>
                    </div>
                @endif

                <input type="file" id="photos-input" name="photos[]" multiple accept="image/*" class="hidden">
                <div id="photo-preview" class="grid grid-cols-4 gap-2 mt-2 hidden"></div>
            </div>

            <div class="flex items-center gap-4 pt-2">
                <button type="submit" class="bg-[#4A40E0] text-white rounded-xl px-8 py-2.5 text-sm font-semibold hover:bg-[#3d35c7] transition-colors">
                    Сохранить и отправить на проверку
                </button>
                <a href="{{ route('my-events') }}" class="text-sm text-gray-500 hover:text-gray-700">Отмена</a>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
    // ── Превью новых фото ─────────────────────────────────────
    const photosInput  = document.getElementById('photos-input');
    const photoPreview = document.getElementById('photo-preview');
    const photoDrop    = document.getElementById('photo-drop');

    if (photosInput) {
        photosInput.addEventListener('change', renderPreviews);
        if (photoDrop) {
            photoDrop.addEventListener('dragover', e => { e.preventDefault(); photoDrop.classList.add('border-[#4A40E0]'); });
            photoDrop.addEventListener('dragleave', () => photoDrop.classList.remove('border-[#4A40E0]'));
            photoDrop.addEventListener('drop', e => {
                e.preventDefault();
                const dt = new DataTransfer();
                [...e.dataTransfer.files].slice(0, 8).forEach(f => dt.items.add(f));
                photosInput.files = dt.files;
                renderPreviews();
            });
        }
    }

    function renderPreviews() {
        if (!photoPreview) return;
        photoPreview.innerHTML = '';
        const files = [...photosInput.files].slice(0, 8);
        if (!files.length) { photoPreview.classList.add('hidden'); return; }
        photoPreview.classList.remove('hidden');
        files.forEach(file => {
            const reader = new FileReader();
            reader.onload = ev => {
                const div = document.createElement('div');
                div.className = 'relative rounded-xl overflow-hidden aspect-square bg-gray-100';
                div.innerHTML = `<img src="${ev.target.result}" class="w-full h-full object-cover">`;
                photoPreview.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    }

    // ── Соцсети ───────────────────────────────────────────────
    let socialIdx = {{ $event->socials->count() ?: 1 }};
    function addSocial() {
        const list = document.getElementById('socials-list');
        const div = document.createElement('div');
        div.className = 'flex gap-2 social-row';
        div.innerHTML = `
            <select name="socials[${socialIdx}][platform]" class="w-36 border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0]">
                <option value="">Платформа</option>
                <option value="VK">VK</option><option value="Telegram">Telegram</option>
                <option value="Instagram">Instagram</option><option value="YouTube">YouTube</option>
                <option value="TikTok">TikTok</option><option value="WhatsApp">WhatsApp</option>
                <option value="Сайт">Сайт</option>
            </select>
            <input type="url" name="socials[${socialIdx}][url]" placeholder="https://..." class="flex-1 border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0]">
            <button type="button" onclick="this.closest('.social-row').remove()" class="text-gray-400 hover:text-red-500 px-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>`;
        list.appendChild(div);
        socialIdx++;
    }

    // Flatpickr
    flatpickr("#event_date_display", {
        locale: "ru", enableTime: true, dateFormat: "d.m.Y H:i", time_24hr: true,
        defaultDate: "{{ \Carbon\Carbon::parse($event->event_date)->format('Y-m-d H:i') }}",
        onChange(dates) {
            if (!dates[0]) return;
            const d = dates[0], p = n => String(n).padStart(2,'0');
            document.getElementById('event_date_hidden').value =
                `${d.getFullYear()}-${p(d.getMonth()+1)}-${p(d.getDate())} ${p(d.getHours())}:${p(d.getMinutes())}:00`;
        }
    });
    document.getElementById('event_date_hidden').value = '{{ \Carbon\Carbon::parse($event->event_date)->format('Y-m-d H:i:s') }}';

    // Цена
    function setPriceType(type) {
        ['price-fixed','price-range'].forEach(id => document.getElementById(id).classList.add('hidden'));
        ['tab-free','tab-fixed','tab-range'].forEach(id => document.getElementById(id).classList.remove('active'));
        if (type === 'fixed') {
            document.getElementById('price-fixed').classList.remove('hidden');
            document.getElementById('tab-fixed').classList.add('active');
        } else if (type === 'range') {
            document.getElementById('price-range').classList.remove('hidden');
            document.getElementById('tab-range').classList.add('active');
        } else {
            document.getElementById('tab-free').classList.add('active');
        }
    }
    @if($event->price_to) setPriceType('range');
    @elseif($event->price) setPriceType('fixed');
    @else setPriceType('free');
    @endif

    // Карта
    const initLat = {{ $event->lat ?? 55.1540 }};
    const initLng = {{ $event->lng ?? 61.4026 }};
    const map = L.map('pick-map', { center: [initLat, initLng], zoom: {{ ($event->lat && $event->lng) ? 15 : 12 }}, zoomControl: true });
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap contributors' }).addTo(map);

    let marker = {{ ($event->lat && $event->lng) ? 'true' : 'false' }}
        ? L.marker([initLat, initLng]).addTo(map) : null;

    function setMarker(lat, lng) {
        if (marker) marker.remove();
        marker = L.marker([lat, lng]).addTo(map);
        document.getElementById('lat').value = lat;
        document.getElementById('lng').value = lng;
    }

    function setStatus(text, ok) {
        document.getElementById('map-status-text').textContent = text;
        document.getElementById('map-status').className = 'flex items-center gap-2 text-xs mb-3 ' + (ok ? 'text-emerald-600' : 'text-gray-400');
    }

    map.on('click', async function(e) {
        const { lat, lng } = e.latlng;
        setMarker(lat, lng);
        setStatus('Определяем адрес...', false);
        map.setView([lat, lng], 16);
        try {
            const r = await fetch(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json&accept-language=ru`, { headers: { 'User-Agent': 'GdeDvizh/1.0' } });
            const data = await r.json();
            if (data && data.address) {
                const a = data.address;
                const parts = [a.road, a.house_number, a.city || a.town || a.village].filter(Boolean);
                const pretty = parts.join(', ') || data.display_name.split(',').slice(0,3).join(',').trim();
                const addrInput = document.getElementById('address-input');
                if (!addrInput.dataset.manualEdit) addrInput.value = pretty;
                setStatus(pretty, true);
            }
        } catch(e) { setStatus(`${lat.toFixed(5)}, ${lng.toFixed(5)}`, true); }
    });

    // Автодополнение
    const addrInput  = document.getElementById('address-input');
    const suggestBox = document.getElementById('suggestions');
    let suggestTimer = null;

    addrInput.addEventListener('input', function() {
        this.dataset.manualEdit = '1';
        clearTimeout(suggestTimer);
        const q = this.value.trim();
        if (q.length < 3) { suggestBox.classList.add('hidden'); return; }
        suggestTimer = setTimeout(() => fetchSuggestions(q), 350);
    });

    async function fetchSuggestions(q) {
        try {
            const r = await fetch(`https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(q)}&format=json&limit=5&addressdetails=1&accept-language=ru`, { headers: { 'User-Agent': 'GdeDvizh/1.0' } });
            const data = await r.json();
            if (!data.length) { suggestBox.classList.add('hidden'); return; }
            suggestBox.innerHTML = data.map(item => {
                const a = item.address || {};
                const main = [a.road, a.house_number].filter(Boolean).join(', ') || item.display_name.split(',')[0];
                const sub  = [a.city || a.town || a.village, a.state].filter(Boolean).join(', ');
                return `<div class="sug-item" data-lat="${item.lat}" data-lon="${item.lon}" data-label="${main}">
                    <svg style="flex-shrink:0;margin-top:2px" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    <div><div class="sug-main">${main}</div><div class="sug-sub">${sub}</div></div>
                </div>`;
            }).join('');
            suggestBox.classList.remove('hidden');
            suggestBox.querySelectorAll('.sug-item').forEach(el => {
                el.addEventListener('click', () => {
                    const lat = parseFloat(el.dataset.lat), lng = parseFloat(el.dataset.lon);
                    addrInput.value = el.dataset.label;
                    addrInput.dataset.manualEdit = '';
                    suggestBox.classList.add('hidden');
                    setMarker(lat, lng);
                    setStatus(el.dataset.label, true);
                    map.setView([lat, lng], 16);
                });
            });
        } catch(e) { suggestBox.classList.add('hidden'); }
    }

    document.addEventListener('click', e => {
        if (!addrInput.contains(e.target) && !suggestBox.contains(e.target)) suggestBox.classList.add('hidden');
    });

    document.getElementById('address-override').addEventListener('change', function() {
        document.getElementById('display-address-block').classList.toggle('hidden', !this.checked);
    });
    </script>
    @endpush
</x-app-layout>
