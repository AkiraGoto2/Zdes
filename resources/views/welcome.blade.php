<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'ГдеДвиж') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <!-- Leaflet MarkerCluster -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />
    <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>

    <style>
        html, body { height: 100%; margin: 0; }
        #map { position: absolute; inset: 0; width: 100%; height: 100%; z-index: 0; }

        /* Кастомный маркер-кружок */
        .event-marker {
            width: 36px; height: 36px;
            border-radius: 50%;
            background: #4A40E0;
            border: 3px solid white;
            box-shadow: 0 2px 8px rgba(74,64,224,.45);
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            transition: transform .15s;
        }
        .event-marker:hover { transform: scale(1.15); }
        .event-marker svg { width: 16px; height: 16px; fill: white; }

        /* Кластер */
        .marker-cluster-small, .marker-cluster-medium, .marker-cluster-large {
            background-color: rgba(74,64,224,.18) !important;
        }
        .marker-cluster-small div, .marker-cluster-medium div, .marker-cluster-large div {
            background-color: #4A40E0 !important;
            color: white !important;
            font-weight: 600 !important;
            font-size: 13px !important;
            font-family: 'Instrument Sans', sans-serif !important;
        }

        /* Popup карточка */
        .leaflet-popup-content-wrapper {
            border-radius: 16px !important;
            padding: 0 !important;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(0,0,0,.15) !important;
            border: none !important;
        }
        .leaflet-popup-content { margin: 0 !important; width: auto !important; min-width: 240px; }
        .leaflet-popup-tip-container { display: none; }
        .leaflet-popup-close-button {
            top: 8px !important; right: 8px !important;
            color: #6b7280 !important;
            font-size: 18px !important;
            z-index: 10;
        }

        /* Поиск + фильтры поверх карты */
        #map-overlay {
            position: absolute;
            top: 16px; left: 50%;
            transform: translateX(-50%);
            z-index: 1000;
            width: calc(100% - 40px);
            max-width: 700px;
            pointer-events: none;
        }
        #map-overlay > * { pointer-events: auto; }

        /* Панель «Nearby Today» */
        #side-panel {
            position: absolute;
            top: 16px; left: 16px;
            z-index: 999;
            width: 300px;
        }

        /* Скрыть attribution на мобиле */
        @media(max-width:600px) { .leaflet-control-attribution { display:none; } }
    </style>
</head>
<body class="bg-[#FDFDFC] text-[#0c0c0c] flex flex-col" style="height:100vh;overflow:hidden;">

<!-- HEADER -->
<header class="w-full border-b border-gray-100 bg-white flex-shrink-0" style="height:65px;z-index:1100;position:relative;">
    <div class="max-w-[1280px] mx-auto h-full flex items-center justify-between px-6">
        <div class="flex items-center gap-8">
            <a href="/" class="block">
                <x-application-logo style="width:100px;height:auto;" />
            </a>
            <nav class="flex items-center gap-6">
                <a href="{{ url('/') }}" class="text-[14px] font-medium text-[#4A40E0] underline underline-offset-4">Главная</a>
                <a href="{{ url('/events') }}" class="text-[14px] font-medium text-[#475569] hover:text-[#4A40E0] transition">События</a>
                @auth
                    <a href="{{ url('/my-events') }}" class="text-[14px] font-medium text-[#475569] hover:text-[#4A40E0] transition">Мои события</a>
                @endauth
            </nav>
        </div>
        @if(Route::has('login'))
            <nav class="flex items-center gap-3">
                @auth
                    <a href="{{ url('/dashboard') }}" class="bg-[#4A40E0] text-white rounded-[12px] text-[14px] py-2 px-5 font-semibold hover:bg-[#3d35c7] transition-colors">Профиль</a>
                @else
                    @if(Route::has('register'))
                        <a href="{{ route('register') }}" class="text-[#475569] rounded-[12px] text-[14px] py-2 px-5 font-semibold hover:bg-gray-100 hover:text-[#4A40E0] transition-colors">Зарегистрироваться</a>
                    @endif
                    <a href="{{ route('login') }}" class="bg-[#4A40E0] text-white rounded-[12px] text-[14px] py-2 px-5 font-semibold hover:bg-[#3d35c7] transition-colors">Войти</a>
                @endauth
            </nav>
        @endif
    </div>
</header>

<!-- КАРТА (занимает всё оставшееся место) -->
<div style="flex:1;position:relative;overflow:hidden;">
    <div id="map"></div>

    <!-- ПОИСК + ФИЛЬТРЫ поверх карты -->
    <div id="map-overlay">
        <!-- Строка поиска -->
        <div class="flex gap-2">
            <div class="flex-1 bg-white rounded-2xl shadow-lg border border-gray-100 flex items-center px-4 gap-2">
                <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input id="search-input" type="text" placeholder="Поиск событий..."
                    class="flex-1 py-3 text-sm bg-transparent outline-none placeholder-gray-400">
                <button id="search-clear" onclick="clearSearch()" class="hidden text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <button onclick="toggleFilters()"
                class="bg-white rounded-2xl shadow-lg border border-gray-100 px-4 py-3 flex items-center gap-2 text-sm font-medium text-gray-600 hover:text-[#4A40E0] transition-colors"
                id="filter-btn">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                </svg>
                Фильтры
                <span id="filter-count" class="hidden bg-[#4A40E0] text-white text-xs rounded-full w-4 h-4 flex items-center justify-center font-bold"></span>
            </button>
        </div>

        <!-- Фильтры (скрыты по умолчанию) -->
        <div id="filters-panel" class="hidden mt-2 bg-white rounded-2xl shadow-lg border border-gray-100 p-4">
            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1">Категория</label>
                    <select id="f-category" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0]">
                        <option value="">Все</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1">Когда</label>
                    <select id="f-date" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0]">
                        <option value="">Любая дата</option>
                        <option value="today">Сегодня</option>
                        <option value="week">На этой неделе</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1">Вход</label>
                    <select id="f-price" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0]">
                        <option value="">Любая цена</option>
                        <option value="free">Бесплатно</option>
                        <option value="paid">Платно</option>
                    </select>
                </div>
            </div>
            <div class="flex justify-end mt-3">
                <button onclick="resetFilters()" class="text-xs text-gray-400 hover:text-gray-600 mr-4">Сбросить</button>
                <button onclick="applyFilters()" class="bg-[#4A40E0] text-white rounded-xl px-5 py-2 text-xs font-semibold hover:bg-[#3d35c7] transition-colors">Применить</button>
            </div>
        </div>

        <!-- Быстрые теги категорий -->
        <div class="flex gap-2 mt-2 flex-wrap" id="quick-tags">
            <button onclick="quickFilter('')" data-cat=""
                class="quick-tag bg-[#4A40E0] text-white rounded-full px-4 py-1.5 text-xs font-semibold shadow transition-all">
                Все события
            </button>
            @foreach($categories->take(5) as $cat)
                <button onclick="quickFilter('{{ $cat->id }}')" data-cat="{{ $cat->id }}"
                    class="quick-tag bg-white text-gray-600 border border-gray-200 rounded-full px-4 py-1.5 text-xs font-semibold shadow-sm hover:border-[#4A40E0] hover:text-[#4A40E0] transition-all">
                    {{ $cat->name }}
                </button>
            @endforeach
        </div>
    </div>

    <!-- БОКОВАЯ ПАНЕЛЬ: ближайшие события -->
    <div id="side-panel">
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="flex items-center justify-between px-4 pt-4 pb-2">
                <span class="text-sm font-semibold">Ближайшие сегодня</span>
                <a href="{{ route('events') }}" class="text-xs text-[#4A40E0] font-medium hover:underline">Все →</a>
            </div>
            <div id="nearby-list" class="divide-y divide-gray-50">
                <div class="px-4 py-3 text-xs text-gray-400">Загружаем события...</div>
            </div>
        </div>

        @auth
            <a href="{{ route('events.create') }}"
               class="mt-2 flex items-center justify-center gap-2 bg-[#4A40E0] text-white rounded-2xl py-3 text-sm font-semibold hover:bg-[#3d35c7] transition-colors shadow-lg">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Создать событие
            </a>
        @endauth
    </div>

    <!-- Кнопки зума (кастомные) -->
    <div class="absolute bottom-6 left-4 z-[999] flex flex-col gap-1">
        <button onclick="map.zoomIn()" class="w-10 h-10 bg-white rounded-xl shadow-lg border border-gray-200 flex items-center justify-center text-gray-600 hover:text-[#4A40E0] text-xl font-light transition-colors">+</button>
        <button onclick="map.zoomOut()" class="w-10 h-10 bg-white rounded-xl shadow-lg border border-gray-200 flex items-center justify-center text-gray-600 hover:text-[#4A40E0] text-xl font-light transition-colors">−</button>
    </div>
</div>

<script>
// ── КАРТА ─────────────────────────────────────────────────────────────
const CENTER = [55.1540, 61.4026];
const map = L.map('map', {
    center: CENTER, zoom: 12,
    minZoom: 5, maxZoom: 19,
    zoomControl: false,
    attributionControl: true
});

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
}).addTo(map);

// ── КЛАСТЕР ──────────────────────────────────────────────────────────
const cluster = L.markerClusterGroup({
    showCoverageOnHover: false,
    maxClusterRadius: 50,
    iconCreateFunction: function(c) {
        return L.divIcon({
            html: `<div style="width:42px;height:42px;border-radius:50%;background:#4A40E0;border:3px solid white;box-shadow:0 2px 10px rgba(74,64,224,.4);display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:14px;font-family:'Instrument Sans',sans-serif;">${c.getChildCount()}</div>`,
            className: '',
            iconSize: [42, 42],
            iconAnchor: [21, 21]
        });
    }
});
map.addLayer(cluster);

// ── ИКОНКИ ПО КАТЕГОРИЯМ ──────────────────────────────────────────────
const CAT_ICONS = {
    default: { bg: '#4A40E0', svg: '<path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>' },
};

function makeMarkerIcon(color) {
    color = color || '#4A40E0';
    return L.divIcon({
        html: `<div style="width:34px;height:34px;border-radius:50%;background:${color};border:3px solid white;box-shadow:0 2px 8px rgba(0,0,0,.25);display:flex;align-items:center;justify-content:center;">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="white"><circle cx="12" cy="12" r="4"/></svg>
        </div>`,
        className: '',
        iconSize: [34, 34],
        iconAnchor: [17, 17],
        popupAnchor: [0, -20]
    });
}

// ── POPUP HTML ────────────────────────────────────────────────────────
function popupHtml(e) {
    const free = e.price === 'Бесплатно';
    return `
    <div style="font-family:'Instrument Sans',sans-serif;min-width:240px;max-width:280px;">
        <div style="background:#4A40E0;padding:12px 16px 10px;">
            <span style="display:inline-block;background:rgba(255,255,255,.2);color:white;font-size:10px;font-weight:600;border-radius:20px;padding:2px 10px;margin-bottom:6px;">${e.category}</span>
            <div style="color:white;font-size:15px;font-weight:700;line-height:1.3;">${e.name}</div>
        </div>
        <div style="padding:12px 16px;">
            <div style="display:flex;align-items:center;gap:6px;color:#6b7280;font-size:12px;margin-bottom:4px;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                ${e.event_date}
            </div>
            <div style="display:flex;align-items:center;gap:6px;color:#6b7280;font-size:12px;margin-bottom:10px;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                ${e.address}
            </div>
            <div style="display:flex;align-items:center;justify-content:space-between;">
                <span style="font-size:15px;font-weight:700;color:${free ? '#059669' : '#0c0c0c'};">${e.price}</span>
                <a href="${e.url}" style="background:#4A40E0;color:white;border-radius:10px;padding:7px 16px;font-size:12px;font-weight:700;text-decoration:none;">Подробнее →</a>
            </div>
        </div>
    </div>`;
}

// ── ДАННЫЕ ────────────────────────────────────────────────────────────
let allEvents = [];
let searchTimer = null;
let activeFilters = { q: '', category: '', date: '', filter: '' };

async function loadEvents() {
    const params = new URLSearchParams();
    if (activeFilters.q)        params.set('q',        activeFilters.q);
    if (activeFilters.category) params.set('category', activeFilters.category);
    if (activeFilters.date)     params.set('date',     activeFilters.date);
    if (activeFilters.filter)   params.set('filter',   activeFilters.filter);

    try {
        const r = await fetch(`/api/map-events?${params}`);
        allEvents = await r.json();
        renderMarkers();
        renderNearby();
    } catch(e) { console.error(e); }
}

function renderMarkers() {
    cluster.clearLayers();
    allEvents.forEach(e => {
        const m = L.marker([e.lat, e.lng], { icon: makeMarkerIcon() });
        m.bindPopup(popupHtml(e), { maxWidth: 300, minWidth: 240, closeButton: true });
        cluster.addLayer(m);
    });
}

function renderNearby() {
    const list = document.getElementById('nearby-list');
    const shown = allEvents.slice(0, 4);
    if (!shown.length) {
        list.innerHTML = '<div class="px-4 py-3 text-xs text-gray-400">Событий не найдено</div>';
        return;
    }
    list.innerHTML = shown.map(e => `
        <a href="${e.url}" class="flex items-center gap-3 px-4 py-2.5 hover:bg-gray-50 transition-colors group">
            <div style="width:36px;height:36px;border-radius:10px;background:#EEF2FF;flex-shrink:0;display:flex;align-items:center;justify-content:center;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#4A40E0" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div style="min-width:0;">
                <div style="font-size:13px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" class="group-hover:text-[#4A40E0] transition-colors">${e.name}</div>
                <div style="font-size:11px;color:#9ca3af;">${e.event_date} · ${e.address.split(',')[0]}</div>
            </div>
        </a>
    `).join('');
}

// ── ПОИСК ─────────────────────────────────────────────────────────────
document.getElementById('search-input').addEventListener('input', function() {
    const val = this.value.trim();
    document.getElementById('search-clear').classList.toggle('hidden', !val);
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        activeFilters.q = val;
        loadEvents();
    }, 350);
});

function clearSearch() {
    document.getElementById('search-input').value = '';
    document.getElementById('search-clear').classList.add('hidden');
    activeFilters.q = '';
    loadEvents();
}

// ── ФИЛЬТРЫ ───────────────────────────────────────────────────────────
function toggleFilters() {
    document.getElementById('filters-panel').classList.toggle('hidden');
}

function applyFilters() {
    activeFilters.category = document.getElementById('f-category').value;
    activeFilters.date     = document.getElementById('f-date').value;
    activeFilters.filter   = document.getElementById('f-price').value;

    // Синхронизируем быстрые теги
    syncQuickTags(activeFilters.category);

    // Счётчик фильтров
    const cnt = [activeFilters.category, activeFilters.date, activeFilters.filter].filter(Boolean).length;
    const badge = document.getElementById('filter-count');
    if (cnt) { badge.textContent = cnt; badge.classList.remove('hidden'); }
    else      { badge.classList.add('hidden'); }

    document.getElementById('filters-panel').classList.add('hidden');
    loadEvents();
}

function resetFilters() {
    document.getElementById('f-category').value = '';
    document.getElementById('f-date').value = '';
    document.getElementById('f-price').value = '';
    activeFilters = { q: activeFilters.q, category: '', date: '', filter: '' };
    document.getElementById('filter-count').classList.add('hidden');
    syncQuickTags('');
    loadEvents();
}

// ── БЫСТРЫЕ ТЕГИ ──────────────────────────────────────────────────────
function quickFilter(catId) {
    activeFilters.category = catId;
    document.getElementById('f-category').value = catId;
    syncQuickTags(catId);
    loadEvents();
}

function syncQuickTags(catId) {
    document.querySelectorAll('.quick-tag').forEach(btn => {
        const active = btn.dataset.cat == catId;
        btn.className = active
            ? 'quick-tag bg-[#4A40E0] text-white rounded-full px-4 py-1.5 text-xs font-semibold shadow transition-all'
            : 'quick-tag bg-white text-gray-600 border border-gray-200 rounded-full px-4 py-1.5 text-xs font-semibold shadow-sm hover:border-[#4A40E0] hover:text-[#4A40E0] transition-all';
    });
}

// ── СТАРТ ─────────────────────────────────────────────────────────────
loadEvents();
</script>
</body>
</html>
