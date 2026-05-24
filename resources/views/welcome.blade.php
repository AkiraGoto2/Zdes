<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'ГдеДвиж') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />
    <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
    <style>
        html,body{height:100%;margin:0;}
        #map{position:absolute;inset:0;width:100%;height:100%;z-index:0;}

        .leaflet-popup-content-wrapper{border-radius:16px!important;padding:0!important;overflow:hidden;box-shadow:0 8px 32px rgba(0,0,0,.15)!important;}
        .leaflet-popup-content{margin:0!important;min-width:240px;}
        .leaflet-popup-tip-container{display:none;}
        .leaflet-popup-close-button{top:8px!important;right:8px!important;color:#6b7280!important;font-size:18px!important;z-index:10;}
        .marker-cluster-small div,.marker-cluster-medium div,.marker-cluster-large div{background:#4A40E0!important;color:white!important;font-weight:700!important;}
        .marker-cluster-small,.marker-cluster-medium,.marker-cluster-large{background:rgba(74,64,224,.2)!important;}
        .leaflet-control-attribution{display:none!important;}

        /* Поиск — центр, учитывает боковую панель слева и погоду справа */
        #map-overlay{
            position:absolute;
            top:12px;
            left:300px;          /* отступ от боковой панели (280px + зазор) */
            right:200px;         /* отступ от погоды (160px + зазор) */
            z-index:1000;
            pointer-events:none;
        }
        #map-overlay>*{pointer-events:auto;}
        #search-input{outline:none!important;box-shadow:none!important;}
        #search-input:focus{outline:none!important;box-shadow:none!important;}

        /* Боковая панель — левый верхний */
        #side-panel{
            position:absolute;top:12px;left:12px;z-index:999;width:276px;
        }

        /* Погода — правый верхний */
        #weather-widget{
            position:absolute;top:12px;right:12px;z-index:1000;
        }

        /* Подсказки поиска */
        #suggestions2{
            position:absolute;top:100%;left:0;right:0;z-index:9999;
            background:white;border:1px solid #e5e7eb;border-radius:12px;
            box-shadow:0 8px 24px rgba(0,0,0,.1);margin-top:4px;
            max-height:200px;overflow-y:auto;
        }
        .sug2{padding:9px 14px;cursor:pointer;font-size:13px;display:flex;align-items:center;gap:8px;border-bottom:1px solid #f3f4f6;}
        .sug2:last-child{border-bottom:none;}
        .sug2:hover{background:#f5f3ff;}

        /* Планшет ~1024px — боковая панель уже, поиск перецентровывается */
        @media(max-width:1100px){
            #map-overlay{left:295px;right:185px;}
        }

        /* Мобайл ~768px — скрываем боковую панель, поиск на всю ширину */
        @media(max-width:768px){
            #side-panel{display:none;}
            #map-overlay{left:8px;right:8px;top:8px;}
            #weather-widget{
                top:auto;
                bottom:72px;
                right:8px;
            }
        }

        /* 320px — погода только градус */
        @media(max-width:400px){
            #weather-widget{display:none;}
        }

        .quick-tag-wrap{display:flex;gap:8px;margin-top:8px;flex-wrap:wrap;}
        @media(max-width:400px){
            .quick-tag-wrap{display:none;}
        }
    </style>
</head>
<body class="bg-[#FDFDFC] flex flex-col" style="height:100vh;overflow:hidden;">

{{-- ШАПКА --}}
<header class="w-full border-b border-gray-100 bg-white flex-shrink-0 sticky top-0 z-[1100]" x-data="{ open: false }" style="height:65px;">
    <div class="max-w-[1280px] mx-auto h-full flex items-center justify-between px-4 sm:px-6">
        <div class="flex items-center gap-6 sm:gap-8">
            <a href="/"><x-application-logo style="width:90px;height:auto;" /></a>
            <nav class="hidden md:flex items-center gap-6">
                <a href="{{ url('/') }}"       class="text-[14px] font-medium text-[#4A40E0] underline underline-offset-4">Главная</a>
                <a href="{{ url('/events') }}" class="text-[14px] font-medium text-[#475569] hover:text-[#4A40E0] transition">События</a>
                @auth <a href="{{ url('/my-events') }}" class="text-[14px] font-medium text-[#475569] hover:text-[#4A40E0] transition">Мои события</a> @endauth
            </nav>
        </div>
        <div class="hidden md:flex items-center gap-3">
            @auth
                <a href="{{ url('/dashboard') }}" class="bg-[#4A40E0] text-white rounded-[12px] text-[14px] py-2 px-5 font-semibold hover:bg-[#3d35c7] transition-colors">Профиль</a>
            @else
                @if(Route::has('register')) <a href="{{ route('register') }}" class="text-[#475569] rounded-[12px] text-[14px] py-2 px-5 font-semibold hover:bg-gray-100 transition-colors">Регистрация</a> @endif
                <a href="{{ route('login') }}" class="bg-[#4A40E0] text-white rounded-[12px] text-[14px] py-2 px-5 font-semibold hover:bg-[#3d35c7] transition-colors">Войти</a>
            @endauth
        </div>
        <button @click="open=!open" class="md:hidden p-2 rounded-xl text-gray-500 hover:bg-gray-100">
            <svg x-show="!open" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            <svg x-show="open" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
    {{-- Мобильное меню --}}
    <div x-show="open" x-cloak class="md:hidden border-t bg-white px-4 py-3 space-y-1 absolute w-full shadow-lg">
        <a href="/"             @click="open=false" class="block px-3 py-2.5 rounded-xl text-sm font-medium text-[#4A40E0] bg-indigo-50">Главная</a>
        <a href="/events"       @click="open=false" class="block px-3 py-2.5 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50">События</a>
        @auth
            <a href="/my-events"    @click="open=false" class="block px-3 py-2.5 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50">Мои события</a>
            <a href="/dashboard"    @click="open=false" class="block px-3 py-2.5 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50">Профиль</a>
        @else
            <a href="{{ route('login') }}"    @click="open=false" class="block px-3 py-2.5 rounded-xl text-sm font-medium bg-[#4A40E0] text-white text-center">Войти</a>
            <a href="{{ route('register') }}" @click="open=false" class="block px-3 py-2.5 rounded-xl text-sm font-medium border border-gray-200 text-center">Регистрация</a>
        @endauth
    </div>
</header>

{{-- КАРТА --}}
<div style="flex:1;position:relative;overflow:hidden;">
    <div id="map"></div>

    {{-- ПОИСК + ФИЛЬТРЫ --}}
    <div id="map-overlay">
        <div class="flex gap-2">
            <div class="flex-1 bg-white rounded-2xl shadow-lg border border-gray-100 flex items-center px-4 gap-2 relative">
                <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input id="search-input" type="text" placeholder="Поиск событий..."
                    class="flex-1 py-3 text-sm bg-transparent outline-none placeholder-gray-400" autocomplete="off">
                <button id="search-clear" onclick="clearSearch()" class="hidden text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
                <div id="suggestions2" class="hidden"></div>
            </div>
            <button onclick="toggleFilters()" id="filter-btn"
                class="bg-white rounded-2xl shadow-lg border border-gray-100 px-4 py-3 flex items-center gap-2 text-sm font-medium text-gray-600 hover:text-[#4A40E0] transition-colors whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
                <span class="hidden sm:inline">Фильтры</span>
                <span id="filter-count" class="hidden bg-[#4A40E0] text-white text-xs rounded-full w-4 h-4 flex items-center justify-center font-bold"></span>
            </button>
        </div>

        {{-- Панель фильтров --}}
        <div id="filters-panel" class="hidden mt-2 bg-white rounded-2xl shadow-lg border border-gray-100 p-4">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Категория</label>
                    <select id="f-category" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0]">
                        <option value="">Все</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Возраст</label>
                    <select id="f-age" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0]">
                        <option value="">Любой</option>
                        @foreach(['0+','6+','12+','16+','18+'] as $a)
                            <option value="{{ $a }}">{{ $a }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Вход</label>
                    <select id="f-price" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0]">
                        <option value="">Любая</option>
                        <option value="free">Бесплатно</option>
                        <option value="paid">Платно</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Дата от</label>
                    <input type="date" id="f-date-from" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0]">
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Дата до</label>
                    <input type="date" id="f-date-to" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0]">
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Время от</label>
                    <input type="time" id="f-time-from" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0]">
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Время до</label>
                    <input type="time" id="f-time-to" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0]">
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-3">
                <button onclick="resetFilters()" class="text-xs text-gray-400 hover:text-gray-600 px-3 py-2">Сбросить</button>
                <button onclick="applyFilters()" class="bg-[#4A40E0] text-white rounded-xl px-5 py-2 text-xs font-semibold hover:bg-[#3d35c7]">Применить</button>
            </div>
        </div>

        {{-- Быстрые теги — скрываются на очень маленьких экранах --}}
        <div class="quick-tag-wrap flex gap-2 mt-2 flex-wrap">
            <button onclick="quickFilter('')" data-cat="" class="quick-tag bg-[#4A40E0] text-white rounded-full px-4 py-1.5 text-xs font-semibold shadow transition-all">Все</button>
            @foreach($categories->take(5) as $cat)
                <button onclick="quickFilter('{{ $cat->id }}')" data-cat="{{ $cat->id }}" class="quick-tag bg-white text-gray-600 border border-gray-200 rounded-full px-4 py-1.5 text-xs font-semibold shadow-sm hover:border-[#4A40E0] hover:text-[#4A40E0] transition-all">{{ $cat->name }}</button>
            @endforeach
        </div>
    </div>

    {{-- Боковая панель: ближайшие сегодня --}}
    <div id="side-panel">
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="flex items-center justify-between px-4 pt-4 pb-2">
                <span class="text-sm font-semibold">Сегодня</span>
                <div class="flex items-center gap-2">
                    <a href="{{ route('events') }}" class="text-xs text-[#4A40E0] font-medium hover:underline">Все →</a>
                    <button onclick="togglePanel()" id="panel-toggle" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                </div>
            </div>
            <div id="nearby-list" class="divide-y divide-gray-50">
                <div class="px-4 py-3 text-xs text-gray-400">Загружаем...</div>
            </div>
        </div>
        @auth
            <a href="{{ route('events.create') }}" class="mt-2 flex items-center justify-center gap-2 bg-[#4A40E0] text-white rounded-2xl py-3 text-sm font-semibold hover:bg-[#3d35c7] transition-colors shadow-lg">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Создать событие
            </a>
        @endauth
    </div>

    {{-- Виджет погоды — правый верхний угол --}}
    <div id="weather-widget">
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 px-4 py-2.5">
            <div id="weather-content" class="flex items-center gap-2.5">
                <div class="text-gray-300 text-xs">Загрузка...</div>
            </div>
        </div>
    </div>

    {{-- Кнопки зума --}}
    <div class="absolute bottom-6 left-4 z-[999] flex flex-col gap-1">
        <button onclick="map.zoomIn()" class="w-10 h-10 bg-white rounded-xl shadow-lg border border-gray-200 flex items-center justify-center text-gray-600 hover:text-[#4A40E0] text-xl font-light">+</button>
        <button onclick="map.zoomOut()" class="w-10 h-10 bg-white rounded-xl shadow-lg border border-gray-200 flex items-center justify-center text-gray-600 hover:text-[#4A40E0] text-xl font-light">−</button>
    </div>
</div>

<script>
// ── КАРТА ─────────────────────────────────────────────────────
const CENTER = [55.1540, 61.4026];
const map = L.map('map', { center: CENTER, zoom: 12, minZoom: 5, maxZoom: 19, zoomControl: false });
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap contributors' }).addTo(map);

const cluster = L.markerClusterGroup({
    showCoverageOnHover: false, maxClusterRadius: 50,
    iconCreateFunction(c) {
        return L.divIcon({
            html: `<div style="width:42px;height:42px;border-radius:50%;background:#4A40E0;border:3px solid white;box-shadow:0 2px 10px rgba(74,64,224,.4);display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:14px;font-family:'Instrument Sans',sans-serif;">${c.getChildCount()}</div>`,
            className: '', iconSize: [42,42], iconAnchor: [21,21]
        });
    }
});
map.addLayer(cluster);

function makeIcon() {
    return L.divIcon({
        html: `<div style="width:34px;height:34px;border-radius:50%;background:#4A40E0;border:3px solid white;box-shadow:0 2px 8px rgba(0,0,0,.25);display:flex;align-items:center;justify-content:center;"><svg viewBox="0 0 24 24" width="12" height="12" fill="white"><circle cx="12" cy="12" r="5"/></svg></div>`,
        className: '', iconSize: [34,34], iconAnchor: [17,17], popupAnchor: [0,-20]
    });
}

function popupHtml(e) {
    const free = e.price === 'Бесплатно';
    const coverHtml = e.cover
        ? `<div style="height:120px;overflow:hidden;"><img src="${e.cover}" style="width:100%;height:100%;object-fit:cover;"></div>`
        : `<div style="height:4px;background:#4A40E0;"></div>`;
    return `<div style="font-family:'Instrument Sans',sans-serif;min-width:240px;max-width:280px;">
        ${coverHtml}
        <div style="padding:12px 14px;">
            <span style="display:inline-block;background:#eef2ff;color:#4A40E0;font-size:10px;font-weight:600;border-radius:20px;padding:2px 8px;margin-bottom:6px;">${e.category}</span>
            <div style="font-size:14px;font-weight:700;line-height:1.3;margin-bottom:8px;">${e.name}</div>
            <div style="color:#6b7280;font-size:11px;margin-bottom:3px;">📅 ${e.event_date}</div>
            <div style="color:#6b7280;font-size:11px;margin-bottom:10px;">📍 ${e.address}</div>
            <div style="display:flex;align-items:center;justify-content:space-between;">
                <span style="font-size:14px;font-weight:700;color:${free?'#059669':'#0c0c0c'};">${e.price}</span>
                <a href="${e.url}" style="background:#4A40E0;color:white;border-radius:9px;padding:6px 14px;font-size:12px;font-weight:700;text-decoration:none;">Подробнее →</a>
            </div>
        </div>
    </div>`;
}

// ── ДАННЫЕ ─────────────────────────────────────────────────────
let allEvents = [];
let activeFilters = { q:'', category:'', age:'', date_from:'', date_to:'', time_from:'', time_to:'', filter:'' };
let searchTimer = null;
let panelCollapsed = false;

async function loadEvents() {
    const p = new URLSearchParams();
    Object.entries(activeFilters).forEach(([k,v]) => { if(v) p.set(k,v); });
    try {
        const r = await fetch(`/api/map-events?${p}`);
        allEvents = await r.json();
        renderMarkers();
        renderNearby();
    } catch(e) {}
}

function renderMarkers() {
    cluster.clearLayers();
    allEvents.forEach(e => {
        const m = L.marker([e.lat, e.lng], { icon: makeIcon() });
        m.bindPopup(popupHtml(e), { maxWidth: 300 });
        cluster.addLayer(m);
    });
}

function renderNearby() {
    const list = document.getElementById('nearby-list');
    // Сортировка по дате (ближайшие сначала)
    const sorted = [...allEvents].sort((a,b) => a.event_date.localeCompare(b.event_date));
    const shown = sorted.slice(0, 5);
    if (!shown.length) { list.innerHTML = '<div class="px-4 py-3 text-xs text-gray-400">Событий нет</div>'; return; }
    list.innerHTML = shown.map(e => `
        <a href="${e.url}" class="flex items-center gap-3 px-4 py-2.5 hover:bg-gray-50 transition-colors group">
            ${e.cover
                ? `<div style="width:36px;height:36px;border-radius:10px;overflow:hidden;flex-shrink:0;"><img src="${e.cover}" style="width:100%;height:100%;object-fit:cover;"></div>`
                : `<div style="width:36px;height:36px;border-radius:10px;background:#EEF2FF;flex-shrink:0;display:flex;align-items:center;justify-content:center;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#4A40E0" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>`
            }
            <div style="min-width:0;">
                <div style="font-size:13px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" class="group-hover:text-[#4A40E0] transition-colors">${e.name}</div>
                <div style="font-size:11px;color:#9ca3af;">${e.event_date}</div>
            </div>
        </a>
    `).join('');
}

// ── ПОИСК С ПОДСКАЗКАМИ ────────────────────────────────────────
const searchInput = document.getElementById('search-input');
const sug2Box     = document.getElementById('suggestions2');

searchInput.addEventListener('input', function() {
    const val = this.value.trim();
    document.getElementById('search-clear').classList.toggle('hidden', !val);
    clearTimeout(searchTimer);
    searchTimer = setTimeout(async () => {
        activeFilters.q = val;
        loadEvents();
        if (val.length >= 2) {
            try {
                const r = await fetch(`/api/map-events?q=${encodeURIComponent(val)}`);
                const data = await r.json();
                if (!data.length) { sug2Box.classList.add('hidden'); return; }
                sug2Box.innerHTML = data.slice(0,5).map(e =>
                    `<div class="sug2" data-lat="${e.lat}" data-lng="${e.lng}" data-name="${e.name}" onclick="flyTo(${e.lat},${e.lng},'${e.name.replace(/'/g,"\\'")}');sug2Box.classList.add('hidden');">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2" style="flex-shrink:0"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        <span style="font-size:13px;">${e.name}</span>
                    </div>`
                ).join('');
                sug2Box.classList.remove('hidden');
            } catch(e) {}
        } else { sug2Box.classList.add('hidden'); }
    }, 350);
});

document.addEventListener('click', e => { if (!searchInput.contains(e.target) && !sug2Box.contains(e.target)) sug2Box.classList.add('hidden'); });

function flyTo(lat, lng, name) {
    map.setView([lat, lng], 15);
    searchInput.value = name;
}

function clearSearch() {
    searchInput.value = '';
    document.getElementById('search-clear').classList.add('hidden');
    sug2Box.classList.add('hidden');
    activeFilters.q = '';
    loadEvents();
}

// ── ФИЛЬТРЫ ─────────────────────────────────────────────────────
function toggleFilters() { document.getElementById('filters-panel').classList.toggle('hidden'); }

function applyFilters() {
    activeFilters.category  = document.getElementById('f-category').value;
    activeFilters.age       = document.getElementById('f-age').value;
    activeFilters.filter    = document.getElementById('f-price').value;
    activeFilters.date_from = document.getElementById('f-date-from').value;
    activeFilters.date_to   = document.getElementById('f-date-to').value;
    activeFilters.time_from = document.getElementById('f-time-from').value;
    activeFilters.time_to   = document.getElementById('f-time-to').value;
    syncQuickTags(activeFilters.category);
    const cnt = Object.values(activeFilters).filter(v => v && v !== activeFilters.q).length;
    const badge = document.getElementById('filter-count');
    if (cnt) { badge.textContent = cnt; badge.classList.remove('hidden'); }
    else { badge.classList.add('hidden'); }
    document.getElementById('filters-panel').classList.add('hidden');
    loadEvents();
}

function resetFilters() {
    ['f-category','f-age','f-price','f-date-from','f-date-to','f-time-from','f-time-to'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = '';
    });
    activeFilters = { q: activeFilters.q, category:'', age:'', date_from:'', date_to:'', time_from:'', time_to:'', filter:'' };
    document.getElementById('filter-count').classList.add('hidden');
    syncQuickTags('');
    loadEvents();
}

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

// ── ПАНЕЛЬ СКРЫТЬ/ПОКАЗАТЬ ─────────────────────────────────────
function togglePanel() {
    panelCollapsed = !panelCollapsed;
    const list = document.getElementById('nearby-list');
    const icon = document.getElementById('panel-toggle');
    list.style.display = panelCollapsed ? 'none' : '';
    icon.style.transform = panelCollapsed ? 'rotate(-90deg)' : '';
}

// ── ПОГОДА ─────────────────────────────────────────────────────
async function loadWeather() {
    try {
        const lat = 55.1540, lon = 61.4026;

        // Получаем название города
        let cityName = 'Челябинск';
        try {
            const geo = await fetch(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lon}&format=json&accept-language=ru`, { headers: { 'User-Agent': 'GdeDvizh/1.0' } });
            const geoData = await geo.json();
            cityName = geoData.address?.city || geoData.address?.town || geoData.address?.village || 'Челябинск';
        } catch(e) {}

        // Погода
        const r = await fetch(`https://api.open-meteo.com/v1/forecast?latitude=${lat}&longitude=${lon}&current=temperature_2m,weathercode,windspeed_10m&timezone=auto`);
        const d = await r.json();
        const cur = d.current;
        const code = cur.weathercode;
        const icons = {
            0:'☀️', 1:'🌤️', 2:'⛅', 3:'☁️', 45:'🌫️', 48:'🌫️',
            51:'🌦️', 53:'🌦️', 55:'🌧️', 61:'🌧️', 63:'🌧️', 65:'🌧️',
            71:'🌨️', 73:'🌨️', 75:'🌨️', 80:'🌦️', 81:'🌧️', 82:'⛈️',
            95:'⛈️', 96:'⛈️', 99:'⛈️'
        };
        const icon = icons[code] || '🌡️';

        // Форматируем дату
        const now = new Date();
        const days = ['вс','пн','вт','ср','чт','пт','сб'];
        const months = ['янв','фев','мар','апр','май','июн','июл','авг','сен','окт','ноя','дек'];
        const dateStr = `${days[now.getDay()]}, ${now.getDate()} ${months[now.getMonth()]}`;

        document.getElementById('weather-content').innerHTML = `
            <span style="font-size:20px;line-height:1;">${icon}</span>
            <div>
                <div style="display:flex;align-items:baseline;gap:6px;">
                    <span style="font-size:16px;font-weight:700;color:#111;">${Math.round(cur.temperature_2m)}°C</span>
                    <span style="font-size:11px;color:#6b7280;">${cityName}</span>
                </div>
                <div style="font-size:10px;color:#9ca3af;">${dateStr} · 💨 ${Math.round(cur.windspeed_10m)} км/ч</div>
            </div>`;
    } catch(e) {
        document.getElementById('weather-content').innerHTML = '<span style="font-size:11px;color:#d1d5db;">Недоступно</span>';
    }
}

loadEvents();
loadWeather();
</script>
</body>
</html>
