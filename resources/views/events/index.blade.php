<x-app-layout>

<div class="max-w-[1280px] mx-auto px-4 sm:px-6 py-6 sm:py-8">

    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold">События</h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ $events->total() }} {{ trans_choice('событие|события|событий', $events->total()) }}</p>
        </div>
        <div class="flex items-center gap-2">
            {{-- Кнопка фильтров — на мобиле и десктопе --}}
            <button id="filter-toggle-btn"
                class="flex items-center gap-1.5 border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-600 hover:bg-gray-50"
                onclick="toggleFilters()">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
                Фильтры
                @if(request()->hasAny(['q','category','filter','age','date_from','date_to','time_from','time_to','city','price_from','price_to']))
                    <span class="bg-[#4A40E0] text-white text-[10px] rounded-full w-4 h-4 flex items-center justify-center font-bold">!</span>
                @endif
            </button>
            @auth
                <a href="{{ route('events.create') }}" class="bg-[#4A40E0] text-white rounded-xl text-sm py-2 px-4 font-semibold hover:bg-[#3d35c7] transition-colors">+ Создать</a>
            @endauth
        </div>
    </div>

    <div id="filter-form" class="{{ request()->hasAny(['q','category','filter','age','date_from','date_to','time_from','time_to','city','price_from','price_to']) ? '' : 'hidden' }} mb-6">
    <form method="GET" action="{{ route('events') }}">
        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">

                {{-- Поиск --}}
                <div class="col-span-2 sm:col-span-3 lg:col-span-4">
                    <input type="text" name="q" value="{{ request('q') }}"
                        class="w-full border border-gray-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0]"
                        placeholder="Поиск по названию или адресу...">
                </div>

                {{-- Город --}}
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Город</label>
                    <input type="text" name="city" value="{{ request('city') }}"
                        class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0]"
                        placeholder="Например: Москва">
                </div>

                {{-- Категория --}}
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Категория</label>
                    <select name="category" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0]">
                        <option value="">Все</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Вход --}}
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Вход</label>
                    <select name="filter" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0]">
                        <option value="">Любая</option>
                        <option value="free"  {{ request('filter') === 'free'  ? 'selected' : '' }}>Бесплатно</option>
                        <option value="paid"  {{ request('filter') === 'paid'  ? 'selected' : '' }}>Платно</option>
                    </select>
                </div>

                {{-- Возраст --}}
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Возраст</label>
                    <select name="age" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0]">
                        <option value="">Любой</option>
                        @foreach(['0+','6+','12+','16+','18+'] as $a)
                            <option value="{{ $a }}" {{ request('age') === $a ? 'selected' : '' }}>{{ $a }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Диапазон цен --}}
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Цена от (₽)</label>
                    <input type="number" name="price_from" value="{{ request('price_from') }}" min="0"
                        class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0]"
                        placeholder="0">
                </div>

                <div>
                    <label class="block text-xs text-gray-400 mb-1">Цена до (₽)</label>
                    <input type="number" name="price_to" value="{{ request('price_to') }}" min="0"
                        class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0]"
                        placeholder="5000">
                </div>

                {{-- Сортировка --}}
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Сортировка</label>
                    <select name="sort" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0]">
                        <option value="soonest" {{ request('sort','soonest') === 'soonest' ? 'selected' : '' }}>Ближайшие сначала</option>
                        <option value="newest"  {{ request('sort') === 'newest'  ? 'selected' : '' }}>Новые сначала</option>
                    </select>
                </div>

                {{-- Дата от --}}
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Дата от</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                        class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0]">
                </div>

                {{-- Дата до --}}
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Дата до</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                        class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0]">
                </div>

                {{-- Время от --}}
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Время от</label>
                    <input type="time" name="time_from" value="{{ request('time_from') }}"
                        class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0]">
                </div>

                {{-- Время до --}}
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Время до</label>
                    <input type="time" name="time_to" value="{{ request('time_to') }}"
                        class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0]">
                </div>

            </div>

            <div class="flex gap-2 mt-4">
                <button type="submit" class="bg-[#4A40E0] text-white rounded-xl px-6 py-2 text-sm font-semibold hover:bg-[#3d35c7] transition-colors">Найти</button>
                @if(request()->hasAny(['q','category','filter','age','date_from','date_to','time_from','time_to','sort','city','price_from','price_to']))
                    <a href="{{ route('events') }}" class="border border-gray-200 text-gray-500 rounded-xl px-4 py-2 text-sm hover:bg-gray-50 transition-colors">Сбросить</a>
                @endif
            </div>
        </div>
    </form>
    </div>

    <script>
    function toggleFilters() {
        const form = document.getElementById('filter-form');
        form.classList.toggle('hidden');
    }
    </script>

    
    @if($events->isEmpty())
        <div class="text-center py-24 text-gray-400">
            <svg class="w-12 h-12 mx-auto mb-4 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            <p class="font-medium">Событий не найдено</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach($events as $event)
                <a href="{{ route('events.show', $event) }}"
                   class="bg-white rounded-2xl border border-gray-200 overflow-hidden hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 flex flex-col">

                    @if($event->photos->first())
                        <div class="h-36 overflow-hidden">
                            <img src="{{ Storage::url($event->photos->first()->path) }}" class="w-full h-full object-cover" alt="{{ $event->name }}">
                        </div>
                    @else
                        <div class="h-36 bg-gradient-to-br from-indigo-50 to-indigo-100 flex items-center justify-center">
                            <svg class="w-10 h-10 text-indigo-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                    @endif

                    <div class="p-4 flex flex-col flex-1">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[11px] font-medium text-[#4A40E0] bg-indigo-50 rounded-full px-2 py-0.5">{{ $event->category->name }}</span>
                            <span class="text-[11px] text-gray-400 font-medium">{{ $event->age }}</span>
                        </div>
                        <h3 class="font-semibold text-[15px] leading-snug line-clamp-2 mb-auto">{{ $event->name }}</h3>
                        <div class="mt-3 space-y-1">
                            <div class="flex items-center gap-1.5 text-xs text-gray-500">
                                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                {{ \Carbon\Carbon::parse($event->event_date)->translatedFormat('d M, H:i') }}
                            </div>
                            <div class="flex items-center gap-1.5 text-xs text-gray-500">
                                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span class="truncate">{{ $event->address }}</span>
                            </div>
                        </div>
                        <div class="mt-3 pt-3 border-t border-gray-100">
                            @if(is_null($event->price))
                                <span class="text-sm font-semibold text-emerald-600">Бесплатно</span>
                            @elseif($event->price_to)
                                <span class="text-sm font-semibold">{{ number_format($event->price,0,'','  ') }} – {{ number_format($event->price_to,0,'','  ') }} ₽</span>
                            @else
                                <span class="text-sm font-semibold">{{ number_format($event->price,0,'','  ') }} ₽</span>
                            @endif
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        @if($events->hasPages())
            <div class="mt-10 flex justify-center">{{ $events->links() }}</div>
        @endif
    @endif
	
</div>
</x-app-layout>
