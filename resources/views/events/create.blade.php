<x-app-layout>
    @push('styles')
        <style>.leaflet-control-attribution{display:none!important;}</style>
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

        <form method="POST" action="{{ route('events.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Название <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required
                    class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0]"
                    placeholder="Например: Виниловый вечер в баре Vinyl">
            </div>

            
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

            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Начало <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input type="text" id="event_date_display" readonly
                            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0] cursor-pointer"
                            placeholder="Дата и время начала">
                        <input type="hidden" name="event_date" id="event_date_hidden" value="{{ old('event_date') }}">
                        <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Окончание <span class="text-xs font-normal text-gray-400">— необязательно</span></label>
                    <div class="relative">
                        <input type="text" id="event_date_end_display" readonly
                            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0] cursor-pointer"
                            placeholder="Дата и время окончания">
                        <input type="hidden" name="event_date_end" id="event_date_end_hidden" value="{{ old('event_date_end') }}">
                        <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                </div>
            </div>

            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Вход</label>
                <input type="hidden" name="price_type" id="price_type_field" value="{{ old('price_type', 'free') }}">
                <div class="flex gap-2 mb-3">
                    <button type="button" id="tab-free"  class="price-tab border border-gray-300 rounded-lg px-4 py-2 text-sm font-medium" onclick="setPriceType('free')">Бесплатно</button>
                    <button type="button" id="tab-fixed" class="price-tab border border-gray-300 rounded-lg px-4 py-2 text-sm font-medium" onclick="setPriceType('fixed')">Фиксированная</button>
                    <button type="button" id="tab-range" class="price-tab border border-gray-300 rounded-lg px-4 py-2 text-sm font-medium" onclick="setPriceType('range')">Диапазон</button>
                </div>
                <div id="price-fixed" class="hidden">
                    <input type="number" name="price" id="price-fixed-input" value="{{ old('price') }}" min="0"
                        class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0]" placeholder="Стоимость в ₽">
                </div>
                <div id="price-range" class="hidden">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs text-gray-400 mb-1">От ₽</label>
                            <input type="number" id="price-from" name="price_range_from" value="{{ old('price') }}" min="0"
                                class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0]" placeholder="500">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 mb-1">До ₽</label>
                            <input type="number" id="price-to" name="price_to" value="{{ old('price_to') }}" min="0"
                                class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0]" placeholder="1500">
                        </div>
                    </div>
                    <p id="price-range-error" class="hidden text-xs text-red-500 mt-1">«До» должно быть больше или равно «От»</p>
                </div>
                @error('price_to')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Описание <span class="text-red-500">*</span></label>
                <textarea name="description" rows="4" required minlength="20"
                    class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0] resize-none"
                    placeholder="Расскажите подробнее о событии...">{{ old('description') }}</textarea>
            </div>

            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Адрес <span class="text-red-500">*</span>
                    <span class="text-xs font-normal text-gray-400 ml-1">— начните вводить, карта обновится автоматически</span>
                </label>
                <div class="relative">
                    <input type="text" name="address" id="address-input" value="{{ old('address') }}"
                        required autocomplete="off"
                        class="w-full border border-gray-300 rounded-xl px-4 py-2.5 pr-10 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0]"
                        placeholder="Город, улица, дом — например: Челябинск, ул. Кирова, 114"
                        onkeydown="if(event.key==='Enter'){event.preventDefault();autoGeocode(this.value.trim());}">
                    <button type="button" onclick="autoGeocode(document.getElementById('address-input').value.trim())"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-[#4A40E0]" title="Найти на карте">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="m21 21-4.35-4.35"/>
                        </svg>
                    </button>
                    <div id="suggestions" class="hidden"></div>
                </div>
            </div>

            
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="text-sm font-medium text-gray-700">Место на карте</label>
                    <span class="text-xs text-gray-400">Выберите адрес из подсказок или кликните на карту</span>
                </div>

                <div id="pick-map" class="mb-3 border border-gray-200"></div>

                
                <div id="map-status" class="flex items-center gap-2 text-xs text-gray-400 mb-3">
                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span id="map-status-text">Метка не поставлена</span>
                </div>

                
                <div id="override-block" class="hidden bg-gray-50 rounded-xl p-3 border border-gray-200">
                    <label class="flex items-start gap-2.5 cursor-pointer">
                        <input type="checkbox" id="address-override" name="address_override" value="1"
                            {{ old('address_override') ? 'checked' : '' }}
                            class="mt-0.5 w-4 h-4 rounded border-gray-300 text-[#4A40E0] focus:ring-[#4A40E0]">
                        <div>
                            <span class="text-sm font-medium text-gray-700">Текстовый адрес отличается от метки на карте</span>
                            <p class="text-xs text-gray-400 mt-0.5">Метка показывает точное место, а адрес — пояснение (например: «вход со двора»)</p>
                        </div>
                    </label>
                    <div id="display-address-block" class="{{ old('address_override') ? '' : 'hidden' }} mt-3">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Отображаемый адрес (виден пользователям)</label>
                        <input type="text" name="display_address" value="{{ old('display_address') }}"
                            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0]"
                            placeholder="Например: Вход с ул. Ленина, у арки">
                    </div>
                </div>

                <input type="hidden" name="lat" id="lat" value="{{ old('lat') }}">
                <input type="hidden" name="lng" id="lng" value="{{ old('lng') }}">
            </div>

            
            <div>
            {{-- Контакты для связи --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Контакты для связи <span class="text-xs font-normal text-gray-400">— телефон, email, Telegram, VK или любой другой способ</span></label>
                <input type="text" name="contact_phone" value="{{ old('contact_phone') }}"
                    class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0]"
                    placeholder="Например: +7 999 000-00-00, @username, email@mail.ru">
            </div>

                <label class="block text-sm font-medium text-gray-700 mb-2">Ссылки на соцсети</label>
                <div id="socials-list" class="space-y-2">
                    <div class="flex gap-2 social-row">
                        <select name="socials[0][platform]" class="w-36 border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0]">
                            <option value="">Платформа</option>
                            <option value="VK">VK</option>
                            <option value="Telegram">Telegram</option>
                            <option value="Instagram">Instagram</option>
                            <option value="YouTube">YouTube</option>
                            <option value="TikTok">TikTok</option>
                            <option value="WhatsApp">WhatsApp</option>
                            <option value="Сайт">Сайт</option>
                        </select>
                        <input type="url" name="socials[0][url]" placeholder="https://..." class="flex-1 border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0]">
                    </div>
                </div>
                <button type="button" onclick="addSocial()" class="mt-2 text-sm text-[#4A40E0] hover:underline flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Добавить ссылку
                </button>
            </div>

            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Фотографии
                    <span class="text-xs font-normal text-gray-400 ml-1">— до 8 файлов, jpg/png/webp, до 5 МБ каждый</span>
                </label>

                <div id="photo-drop"
                    class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center cursor-pointer hover:border-[#4A40E0] hover:bg-indigo-50/30 transition-colors"
                    onclick="document.getElementById('photos-input').click()">
                    <svg class="w-8 h-8 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p class="text-sm text-gray-500">Нажмите или перетащите фото сюда</p>
                    <p class="text-xs text-gray-400 mt-1">Первое фото станет обложкой события</p>
                </div>

                <input type="file" id="photos-input" name="photos[]" multiple accept="image/*" class="hidden">

                <div id="photo-preview" class="grid grid-cols-4 gap-2 mt-3 hidden"></div>
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
    
    let socialIdx = 1;
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

    
    const photosInput   = document.getElementById('photos-input');
    const photoPreview  = document.getElementById('photo-preview');
    const photoDrop     = document.getElementById('photo-drop');

    photosInput.addEventListener('change', renderPreviews);

    
    photoDrop.addEventListener('dragover', e => { e.preventDefault(); photoDrop.classList.add('border-[#4A40E0]','bg-indigo-50/30'); });
    photoDrop.addEventListener('dragleave', () => { photoDrop.classList.remove('border-[#4A40E0]','bg-indigo-50/30'); });
    photoDrop.addEventListener('drop', e => {
        e.preventDefault();
        photoDrop.classList.remove('border-[#4A40E0]','bg-indigo-50/30');
        const dt = new DataTransfer();
        [...e.dataTransfer.files].slice(0,8).forEach(f => dt.items.add(f));
        photosInput.files = dt.files;
        renderPreviews();
    });

    function renderPreviews() {
        photoPreview.innerHTML = '';
        const files = [...photosInput.files].slice(0, 8);
        if (!files.length) { photoPreview.classList.add('hidden'); return; }
        photoPreview.classList.remove('hidden');
        files.forEach((file, i) => {
            const reader = new FileReader();
            reader.onload = ev => {
                const div = document.createElement('div');
                div.className = 'relative rounded-xl overflow-hidden aspect-square bg-gray-100';
                div.innerHTML = `
                    <img src="${ev.target.result}" class="w-full h-full object-cover">
                    ${i === 0 ? '<span style="position:absolute;bottom:4px;left:4px;background:rgba(74,64,224,.85);color:white;font-size:10px;font-weight:600;border-radius:5px;padding:1px 6px;">Обложка</span>' : ''}
                `;
                photoPreview.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    }

    
    let startPicker = flatpickr("#event_date_display", {
        locale: "ru", enableTime: true, dateFormat: "d.m.Y H:i",
        minDate: "today", time_24hr: true,
        @if(old('event_date')) defaultDate: "{{ old('event_date') }}", @endif
        onChange(dates) {
            if (!dates[0]) return;
            const d = dates[0], p = n => String(n).padStart(2,'0');
            document.getElementById('event_date_hidden').value =
                `${d.getFullYear()}-${p(d.getMonth()+1)}-${p(d.getDate())} ${p(d.getHours())}:${p(d.getMinutes())}:00`;
            // Обновляем минимальную дату окончания
            endPicker.set('minDate', dates[0]);
        }
    });

    let endPicker = flatpickr("#event_date_end_display", {
        locale: "ru", enableTime: true, dateFormat: "d.m.Y H:i",
        minDate: "today", time_24hr: true,
        @if(old('event_date_end')) defaultDate: "{{ old('event_date_end') }}", @endif
        onChange(dates) {
            if (!dates[0]) return;
            const d = dates[0], p = n => String(n).padStart(2,'0');
            document.getElementById('event_date_end_hidden').value =
                `${d.getFullYear()}-${p(d.getMonth()+1)}-${p(d.getDate())} ${p(d.getHours())}:${p(d.getMinutes())}:00`;
        }
    });

    
    function setPriceType(type) {
        document.getElementById('price_type_field').value = type;
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
            document.querySelectorAll('[name="price"],[name="price_to"]').forEach(el => el.value = '');
        }
    }
    @if(old('price_type') === 'range') setPriceType('range');
    @elseif(old('price_type') === 'fixed') setPriceType('fixed');
    @elseif(old('price_to')) setPriceType('range');
    @elseif(old('price')) setPriceType('fixed');
    @else setPriceType('free');
    @endif

    document.querySelector('form').addEventListener('submit', function(e) {
        // Валидация: адрес должен содержать город (lat/lng должны быть заполнены через карту/подсказки)
        const latVal = document.getElementById('lat').value;
        const lngVal = document.getElementById('lng').value;
        const addrVal = document.getElementById('address-input').value.trim();
        if (!latVal || !lngVal) {
            e.preventDefault();
            const addrInput = document.getElementById('address-input');
            addrInput.setCustomValidity('Укажите адрес через подсказки или кликните на карту, чтобы поставить метку');
            addrInput.reportValidity();
            addrInput.setCustomValidity('');
            return false;
        }

        const from = document.getElementById('price-from');
        const to   = document.getElementById('price-to');
        const err  = document.getElementById('price-range-error');
        if (from && to && from.value && to.value && parseInt(to.value) < parseInt(from.value)) {
            e.preventDefault();
            err.classList.remove('hidden');
            to.focus();
            return false;
        }
        if (err) err.classList.add('hidden');
    });


    
    const map = L.map('pick-map', { center: [55.1540, 61.4026], zoom: 12, zoomControl: true });
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: ''
    }).addTo(map);

    let marker = null;
    let reverseGeocoding = false; 

    function setMarker(lat, lng) {
        if (marker) marker.remove();
        marker = L.marker([lat, lng]).addTo(map);
        document.getElementById('lat').value = lat;
        document.getElementById('lng').value = lng;
        document.getElementById('override-block').classList.remove('hidden');
    }

    function setStatus(text, ok) {
        const el = document.getElementById('map-status');
        document.getElementById('map-status-text').textContent = text;
        el.className = 'flex items-center gap-2 text-xs mb-3 ' + (ok ? 'text-emerald-600' : 'text-gray-400');
    }

    const DADATA_TOKEN = 'b95f91ab8b6d655ddabe676393bb43b3df308488';

    const oldLat = "{{ old('lat') }}", oldLng = "{{ old('lng') }}";
    if (oldLat && oldLng) {
        setMarker(parseFloat(oldLat), parseFloat(oldLng));
        map.setView([parseFloat(oldLat), parseFloat(oldLng)], 15);
        setStatus("{{ old('address') }}", true);
        document.getElementById('override-block').classList.remove('hidden');
    }

    
    map.on('click', async function(e) {
        const { lat, lng } = e.latlng;
        setMarker(lat, lng);
        setStatus('Определяем адрес...', false);
        map.setView([lat, lng], 16);

        try {
            const r = await fetch('https://suggestions.dadata.ru/suggestions/api/4_1/rs/geolocate/address', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': 'Token ' + DADATA_TOKEN,
                },
                body: JSON.stringify({ lat, lon: lng, count: 1, language: 'ru' })
            });
            const data = await r.json();
            const item = (data.suggestions || [])[0];
            if (item) {
                const d = item.data;
                const city   = d.city || d.settlement || d.region_with_type || '';
                const street = d.street_with_type || '';
                const house  = d.house ? (d.house_type ? d.house_type + ' ' + d.house : d.house) : '';
                const pretty = [city, street, house].filter(Boolean).join(', ') || item.value;
                document.getElementById('address-input').value = pretty;
                setStatus(pretty, true);
            } else {
                setStatus(`${lat.toFixed(5)}, ${lng.toFixed(5)}`, true);
            }
        } catch(e) {
            setStatus(`${lat.toFixed(5)}, ${lng.toFixed(5)}`, true);
        }
    });

    const addrInput  = document.getElementById('address-input');
    const suggestBox = document.getElementById('suggestions');
    let suggestTimer = null;

    addrInput.addEventListener('input', function() {
        clearTimeout(suggestTimer);
        const q = this.value.trim();
        if (q.length < 2) { suggestBox.classList.add('hidden'); return; }
        suggestBox.innerHTML = '<div style="padding:10px 14px;font-size:13px;color:#9ca3af;">Ищем...</div>';
        suggestBox.classList.remove('hidden');
        suggestTimer = setTimeout(() => fetchSuggestions(q), 300);
    });

    async function fetchSuggestions(q) {
        try {
            const r = await fetch('https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/address', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': 'Token ' + DADATA_TOKEN,
                },
                body: JSON.stringify({ query: q, count: 7, language: 'ru' })
            });
            const data = await r.json();
            const items = data.suggestions || [];

            if (!items.length) {
                suggestBox.innerHTML = '<div style="padding:10px 14px;font-size:13px;color:#9ca3af;">Ничего не найдено</div>';
                return;
            }

            suggestBox.innerHTML = items.map(item => {
                const d = item.data;
                const city = d.city || d.settlement || d.region_with_type || '';
                const street = d.street_with_type || '';
                const house  = d.house ? (d.house_type ? d.house_type + ' ' + d.house : d.house) : '';
                const main   = [street, house].filter(Boolean).join(', ') || item.value.split(',')[0];
                const pretty = [city, street, house].filter(Boolean).join(', ') || item.value;
                return `<div class="sug-item"
                    data-lat="${d.geo_lat || ''}" data-lon="${d.geo_lon || ''}"
                    data-label="${pretty}" data-full="${item.value}">
                    <svg style="flex-shrink:0;margin-top:2px" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/>
                    </svg>
                    <div><div class="sug-main">${main || item.value}</div>${city ? `<div class="sug-sub">${city}</div>` : ''}</div>
                </div>`;
            }).join('');

            suggestBox.classList.remove('hidden');

            suggestBox.querySelectorAll('.sug-item').forEach(el => {
                el.addEventListener('mousedown', e => e.preventDefault()); // не теряем фокус
                el.addEventListener('click', () => {
                    const lat = parseFloat(el.dataset.lat);
                    const lng = parseFloat(el.dataset.lon);
                    const label = el.dataset.label;

                    addrInput.value = label;
                    suggestBox.classList.add('hidden');

                    if (lat && lng && !isNaN(lat) && !isNaN(lng)) {
                        setMarker(lat, lng);
                        setStatus(label, true);
                        map.setView([lat, lng], 16);
                    } else {
                        // координат нет — геокодируем через Nominatim как запасной
                        autoGeocodeNominatim(el.dataset.full || label);
                    }
                });
            });
        } catch(e) {
            suggestBox.classList.add('hidden');
        }
    }

    // Запасной геокодер если Dadata не вернула координаты
    async function autoGeocodeNominatim(q) {
        try {
            const r = await fetch(
                `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(q)}&format=json&limit=1&countrycodes=ru`,
                { headers: { 'User-Agent': 'GdeDvizh/1.0' } }
            );
            const data = await r.json();
            if (data.length) {
                const lat = parseFloat(data[0].lat), lng = parseFloat(data[0].lon);
                setMarker(lat, lng);
                map.setView([lat, lng], 15);
            }
        } catch(e) {}
    }

    
    document.addEventListener('click', e => {
        if (!addrInput.contains(e.target) && !suggestBox.contains(e.target)) {
            suggestBox.classList.add('hidden');
        }
    });

    
    document.getElementById('address-override').addEventListener('change', function() {
        document.getElementById('display-address-block').classList.toggle('hidden', !this.checked);
    });
    @if(old('address_override'))
        document.getElementById('display-address-block').classList.remove('hidden');
    @endif
    </script>
    @endpush
</x-app-layout>
