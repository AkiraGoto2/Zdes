<x-app-layout>

<div class="max-w-[1280px] mx-auto px-6 py-8">

    {{-- Заголовок + кнопка создать --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold">События</h1>
            <p class="text-sm text-gray-500 mt-0.5">
                {{ $events->total() }}
                {{ trans_choice('событие|события|событий', $events->total()) }}
            </p>
        </div>

        @auth
            <a href="{{ route('events.create') }}"
               class="bg-[#4A40E0] text-white rounded-xl text-sm py-2.5 px-5 font-semibold hover:bg-[#3d35c7] transition-colors">
                + Создать событие
            </a>
        @endauth
    </div>

    {{-- ФИЛЬТРЫ --}}
    <form method="GET" action="{{ route('events') }}" class="mb-8">

        <div class="bg-white rounded-2xl border border-gray-200 p-4 flex flex-wrap gap-4 items-end">

            {{-- Категория --}}
            <div class="flex-1 min-w-[160px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">
                    Категория
                </label>

                <select name="category"
                        class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0]">

                    <option value="">Все категории</option>

                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}"
                            {{ request('category') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach

                </select>
            </div>

            {{-- Дата --}}
            <div class="flex-1 min-w-[140px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">
                    Когда
                </label>

                <select name="date"
                        class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0]">

                    <option value="">Любая дата</option>

                    <option value="today"
                        {{ request('date') === 'today' ? 'selected' : '' }}>
                        Сегодня
                    </option>

                    <option value="week"
                        {{ request('date') === 'week' ? 'selected' : '' }}>
                        На этой неделе
                    </option>

                </select>
            </div>

            {{-- Цена --}}
            <div class="flex-1 min-w-[130px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">
                    Цена
                </label>

                <select name="filter"
                        class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0]">

                    <option value="">Любая</option>

                    <option value="free"
                        {{ request('filter') === 'free' ? 'selected' : '' }}>
                        Бесплатно
                    </option>

                    <option value="paid"
                        {{ request('filter') === 'paid' ? 'selected' : '' }}>
                        Платно
                    </option>

                </select>
            </div>

            {{-- Возраст --}}
            <div class="flex-1 min-w-[120px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">
                    Возраст
                </label>

                <select name="age"
                        class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#4A40E0]">

                    <option value="">Любой</option>

                    @foreach(['0+','6+','12+','16+','18+'] as $a)
                        <option value="{{ $a }}"
                            {{ request('age') === $a ? 'selected' : '' }}>
                            {{ $a }}
                        </option>
                    @endforeach

                </select>
            </div>

            {{-- Кнопки --}}
            <div class="flex gap-2">

                <button type="submit"
                        class="bg-[#4A40E0] text-white rounded-xl px-5 py-2 text-sm font-semibold hover:bg-[#3d35c7] transition-colors">
                    Найти
                </button>

                @if(request()->hasAny(['category','date','filter','age']))
                    <a href="{{ route('events') }}"
                       class="border border-gray-200 text-gray-500 rounded-xl px-4 py-2 text-sm hover:bg-gray-50 transition-colors">
                        Сбросить
                    </a>
                @endif

            </div>

        </div>

    </form>

    {{-- ЛЕНТА --}}
    @if($events->isEmpty())

        <div class="text-center py-24 text-gray-400">

            <svg class="w-12 h-12 mx-auto mb-4 opacity-40"
                 fill="none"
                 stroke="currentColor"
                 viewBox="0 0 24 24">

                <path stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="1.5"
                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>

            <p class="text-lg font-medium">
                Событий не найдено
            </p>

            <p class="text-sm mt-1">
                Попробуйте изменить фильтры
            </p>

        </div>

    @else

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">

            @foreach($events as $event)

                <a href="{{ route('events.show', $event) }}"
                   class="bg-white rounded-2xl border border-gray-200 overflow-hidden hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 flex flex-col">

                    {{-- Обложка --}}
                    @if($event->photos->first())

                        <div class="h-36 overflow-hidden">

                            <img
                                src="{{ asset('storage/' . $event->photos->first()->path) }}"
                                class="w-full h-full object-cover"
                                alt="{{ $event->name }}"
                            >

                        </div>

                    @else

                        <div class="h-36 bg-gradient-to-br from-indigo-50 to-indigo-100 flex items-center justify-center">

                            <svg class="w-10 h-10 text-indigo-200"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="1.5"
                                      d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>

                        </div>

                    @endif

                    <div class="p-4 flex flex-col flex-1">

                        {{-- Категория + возраст --}}
                        <div class="flex items-center justify-between mb-2">

                            <span class="text-[11px] font-medium text-[#4A40E0] bg-indigo-50 rounded-full px-2 py-0.5">
                                {{ $event->category->name }}
                            </span>

                            <span class="text-[11px] text-gray-400 font-medium">
                                {{ $event->age }}
                            </span>

                        </div>

                        {{-- Название --}}
                        <h3 class="font-semibold text-[15px] leading-snug line-clamp-2 mb-auto">
                            {{ $event->name }}
                        </h3>

                        {{-- Дата и адрес --}}
                        <div class="mt-3 space-y-1">

                            <div class="flex items-center gap-1.5 text-xs text-gray-500">
                                {{ \Carbon\Carbon::parse($event->event_date)->translatedFormat('d M, H:i') }}
                            </div>

                            <div class="flex items-center gap-1.5 text-xs text-gray-500">
                                <span class="truncate">
                                    {{ $event->address }}
                                </span>
                            </div>

                        </div>

                        {{-- Цена --}}
                        <div class="mt-3 pt-3 border-t border-gray-100">

                            @if(!$event->price || $event->price == 0)

                                <span class="text-sm font-semibold text-emerald-600">
                                    Бесплатно
                                </span>

                            @elseif($event->price_to)

                                <span class="text-sm font-semibold text-gray-800">
                                    {{ number_format($event->price, 0, '', ' ') }}
                                    –
                                    {{ number_format($event->price_to, 0, '', ' ') }}
                                    ₽
                                </span>

                            @else

                                <span class="text-sm font-semibold text-gray-800">
                                    {{ number_format($event->price, 0, '', ' ') }} ₽
                                </span>

                            @endif

                        </div>

                    </div>

                </a>

            @endforeach

        </div>

        {{-- Пагинация --}}
        @if($events->hasPages())

            <div class="mt-10 flex justify-center">
                {{ $events->links() }}
            </div>

        @endif

    @endif

</div>

</x-app-layout>