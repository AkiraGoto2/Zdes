<x-app-layout>
<div class="max-w-[1200px] mx-auto px-6 py-8">

    {{-- Заголовок --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold">Панель администратора</h1>
            <p class="text-sm text-gray-500 mt-0.5">Модерация мероприятий</p>
        </div>
        <!-- <div class="flex items-center gap-2 bg-amber-50 border border-amber-200 text-amber-700 rounded-xl px-4 py-2 text-sm font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ $stats['pending'] }} ожидают проверки
        </div> -->
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl px-4 py-3 text-sm flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif

    <!-- {{-- Статистика --}}
    <div class="grid grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-2xl border border-gray-200 p-4 text-center">
            <div class="text-2xl font-bold text-amber-500">{{ $stats['pending'] }}</div>
            <div class="text-xs text-gray-500 mt-0.5">На проверке</div>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-4 text-center">
            <div class="text-2xl font-bold text-emerald-600">{{ $stats['approved'] }}</div>
            <div class="text-xs text-gray-500 mt-0.5">Опубликовано</div>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-4 text-center">
            <div class="text-2xl font-bold text-red-500">{{ $stats['rejected'] }}</div>
            <div class="text-xs text-gray-500 mt-0.5">Отклонено</div>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-4 text-center">
            <div class="text-2xl font-bold text-[#4A40E0]">{{ $stats['users'] }}</div>
            <div class="text-xs text-gray-500 mt-0.5">Пользователей</div>
        </div>
    </div> -->

    {{-- Табы --}}
    <div class="flex gap-1 bg-gray-100 rounded-xl p-1 mb-6 w-fit">
        @foreach([['pending','На проверке','amber'],['approved','Опубликованные','emerald'],['rejected','Отклонённые','red']] as [$key,$label,$color])
            <a href="{{ route('admin.index', ['tab' => $key]) }}"
               class="rounded-lg px-5 py-2 text-sm font-medium transition-colors
               {{ $tab === $key ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                {{ $label }}
                <span class="ml-1.5 text-xs font-semibold
                    {{ $tab === $key ? 'text-[#4A40E0]' : 'text-gray-400' }}">
                    {{ $stats[$key] }}
                </span>
            </a>
        @endforeach
    </div>

    {{-- Список: На проверке --}}
    @if($tab === 'pending')
        @if($pending->isEmpty())
            <div class="text-center py-20 text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-4 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="font-medium">Новых заявок нет</p>
                <p class="text-sm mt-1">Все мероприятия проверены</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach($pending as $event)
                    <div class="bg-white rounded-2xl border border-amber-200 overflow-hidden">
                        <div class="h-1 bg-amber-400"></div>
                        <div class="p-5">
                            <div class="flex items-start gap-4">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap mb-1">
                                        <h3 class="font-semibold text-base">{{ $event->name }}</h3>
                                        <span class="text-xs bg-indigo-50 text-[#4A40E0] rounded-full px-2 py-0.5 font-medium">{{ $event->category->name }}</span>
                                        <span class="text-xs bg-gray-100 text-gray-500 rounded-full px-2 py-0.5">{{ $event->age }}</span>
                                    </div>
                                    <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-500 mb-3">
                                        <span class="flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                            {{ $event->user->name }} {{ $event->user->lastname }}
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            {{ \Carbon\Carbon::parse($event->event_date)->translatedFormat('d M Y, H:i') }}
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            {{ $event->address }}
                                        </span>
                                        <span class="font-medium {{ (!$event->price || $event->price == 0) ? 'text-emerald-600' : 'text-gray-700' }}">
                                            {{ (!$event->price || $event->price == 0) ? 'Бесплатно' : (number_format($event->price,0,'.',' ') . ($event->price_to ? ' – '.number_format($event->price_to,0,'.',' ') : '') . ' ₽') }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-600 leading-relaxed line-clamp-2">{{ $event->description }}</p>
                                    <p class="text-xs text-gray-400 mt-2">Подана {{ $event->created_at->diffForHumans() }}</p>
                                </div>
                            </div>

                            {{-- Кнопки действий --}}
                            <div class="flex items-center gap-3 mt-4 pt-4 border-t border-gray-100">
                                <form method="POST" action="{{ route('admin.events.approve', $event) }}">
                                    @csrf
                                    <button type="submit"
                                        class="bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl px-6 py-2 text-sm font-semibold transition-colors flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        Одобрить
                                    </button>
                                </form>

                                {{-- Форма отклонения с причиной --}}
                                <div x-data="{ open: false }" class="relative">
                                    <button @click="open = !open"
                                        class="border border-red-200 text-red-500 hover:bg-red-50 rounded-xl px-6 py-2 text-sm font-semibold transition-colors flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                        Отклонить
                                    </button>
                                    <div x-show="open" x-cloak
                                         class="absolute left-0 top-full mt-2 bg-white rounded-2xl border border-gray-200 shadow-xl p-4 z-50 w-80">
                                        <form method="POST" action="{{ route('admin.events.reject', $event) }}">
                                            @csrf
                                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Причина отклонения</label>
                                            <textarea name="reason" rows="3" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300 resize-none"
                                                placeholder="Например: спам, некорректный контент...">Не соответствует правилам площадки.</textarea>
                                            <div class="flex gap-2 mt-3">
                                                <button type="submit" class="flex-1 bg-red-500 hover:bg-red-600 text-white rounded-xl py-2 text-sm font-semibold transition-colors">Отклонить</button>
                                                <button type="button" @click="open = false" class="flex-1 border border-gray-200 text-gray-500 rounded-xl py-2 text-sm hover:bg-gray-50 transition-colors">Отмена</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <a href="{{ route('events.show', $event) }}" target="_blank"
                                   class="ml-auto text-xs text-[#4A40E0] border border-indigo-200 rounded-xl px-4 py-2 hover:bg-indigo-50 transition-colors">
                                    Предпросмотр →
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    @endif

    {{-- Список: Опубликованные --}}
    @if($tab === 'approved')
        <div class="space-y-3">
            @forelse($approved as $event)
                <div class="bg-white rounded-2xl border border-gray-200 p-4 flex items-center gap-4">
                    <div class="w-1 self-stretch rounded-full bg-emerald-400 flex-shrink-0"></div>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-sm truncate">{{ $event->name }}</p>
                        <p class="text-xs text-gray-400">{{ $event->category->name }} · {{ \Carbon\Carbon::parse($event->event_date)->translatedFormat('d M Y') }} · {{ $event->user->name }}</p>
                    </div>
                    <form method="POST" action="{{ route('admin.events.reject', $event) }}">
                        @csrf
                        <input type="hidden" name="reason" value="Снято с публикации администратором.">
                        <button type="submit" class="text-xs text-red-400 border border-red-100 rounded-lg px-3 py-1.5 hover:bg-red-50 transition-colors">Снять</button>
                    </form>
                    <a href="{{ route('events.show', $event) }}" class="text-xs text-[#4A40E0] border border-indigo-200 rounded-lg px-3 py-1.5 hover:bg-indigo-50 transition-colors">Открыть</a>
                </div>
            @empty
                <div class="text-center py-16 text-gray-400">Нет опубликованных событий</div>
            @endforelse
            {{ $approved->links() }}
        </div>
    @endif

    {{-- Список: Отклонённые --}}
    @if($tab === 'rejected')
        <div class="space-y-3">
            @forelse($rejected as $event)
                <div class="bg-white rounded-2xl border border-gray-200 p-4 flex items-center gap-4">
                    <div class="w-1 self-stretch rounded-full bg-red-400 flex-shrink-0"></div>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-sm truncate">{{ $event->name }}</p>
                        <p class="text-xs text-gray-400">{{ $event->category->name }} · {{ $event->user->name }} · {{ $event->updated_at->diffForHumans() }}</p>
                    </div>
                    <form method="POST" action="{{ route('admin.events.approve', $event) }}">
                        @csrf
                        <button type="submit" class="text-xs text-emerald-600 border border-emerald-200 rounded-lg px-3 py-1.5 hover:bg-emerald-50 transition-colors">Одобрить</button>
                    </form>
                </div>
            @empty
                <div class="text-center py-16 text-gray-400">Нет отклонённых событий</div>
            @endforelse
            {{ $rejected->links() }}
        </div>
    @endif

</div>
</x-app-layout>
