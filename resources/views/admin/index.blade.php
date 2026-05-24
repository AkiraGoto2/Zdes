<x-app-layout>

<div class="max-w-[1200px] mx-auto px-4 sm:px-6 py-6 sm:py-8">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold">Панель администратора</h1>
            <p class="text-sm text-gray-500 mt-0.5">Модерация мероприятий</p>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl px-4 py-3 text-sm flex items-center gap-2">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Табы --}}
    <div class="flex gap-1 bg-gray-100 rounded-xl p-1 mb-6 w-fit">
        @foreach([['pending','На проверке'],['approved','Опубликованные'],['rejected','Отклонённые']] as [$key,$label])
            <a href="{{ route('admin.index', ['tab' => $key]) }}"
               class="rounded-lg px-4 py-2 text-sm font-medium transition-colors
               {{ $tab === $key ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                {{ $label }}
                <span class="ml-1 text-xs font-semibold {{ $tab === $key ? 'text-[#4A40E0]' : 'text-gray-400' }}">{{ $stats[$key] }}</span>
            </a>
        @endforeach
    </div>

    {{-- НА ПРОВЕРКЕ --}}
    @if($tab === 'pending')
        @if($pending->isEmpty())
            <div class="text-center py-20 text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-4 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="font-medium">Новых заявок нет</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach($pending as $event)
                    <div class="bg-white rounded-2xl border border-amber-200 overflow-hidden">
                        <div class="h-1 bg-amber-400"></div>

                        {{-- Фотографии события --}}
                        @if($event->photos->count())
                            <div class="flex gap-1 p-3 pb-0 overflow-x-auto">
                                @foreach($event->photos->take(5) as $photo)
                                    <div class="flex-shrink-0 w-24 h-16 rounded-lg overflow-hidden bg-gray-100">
                                        <img src="{{ Storage::url($photo->path) }}" class="w-full h-full object-cover" alt="">
                                    </div>
                                @endforeach
                                @if($event->photos->count() > 5)
                                    <div class="flex-shrink-0 w-24 h-16 rounded-lg bg-gray-100 flex items-center justify-center text-sm text-gray-400 font-medium">
                                        +{{ $event->photos->count() - 5 }}
                                    </div>
                                @endif
                            </div>
                        @endif

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
                                            {{ $event->user->name }} {{ $event->user->lastname }} · {{ $event->user->email }}
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
                                    <p x-data="{ expanded: false }"
                                       class="text-sm text-gray-600 leading-relaxed"
                                       :class="expanded ? '' : 'line-clamp-2'">
                                        {{ $event->description }}
                                        <button @click="expanded = !expanded"
                                            class="ml-1 text-xs text-[#4A40E0] hover:underline font-medium"
                                            x-text="expanded ? 'Свернуть' : 'Читать полностью'">
                                        </button>
                                    </p>

                                    {{-- Соцсети --}}
                                    @if($event->socials->count())
                                        <div class="flex flex-wrap gap-1.5 mt-2">
                                            @foreach($event->socials as $s)
                                                <a href="{{ $s->url }}" target="_blank"
                                                   class="text-xs bg-gray-50 border border-gray-200 rounded-lg px-2 py-1 text-gray-600 hover:text-[#4A40E0] transition-colors">
                                                    {{ $s->platform }}
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif

                                    <p class="text-xs text-gray-400 mt-2">Подана {{ $event->created_at->diffForHumans() }}</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 mt-4 pt-4 border-t border-gray-100">
                                <form method="POST" action="{{ route('admin.events.approve', $event) }}">
                                    @csrf
                                    <button type="submit" class="bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl px-6 py-2 text-sm font-semibold transition-colors flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        Одобрить
                                    </button>
                                </form>

                                <div x-data="{ open: false }" class="relative">
                                    <button @click="open = !open" class="border border-red-200 text-red-500 hover:bg-red-50 rounded-xl px-6 py-2 text-sm font-semibold transition-colors flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                        Отклонить
                                    </button>
                                    <div x-show="open" x-cloak @click.outside="open=false"
                                         class="absolute left-0 top-full mt-2 bg-white rounded-2xl border border-gray-200 shadow-xl p-4 z-50 w-80">
                                        <form method="POST" action="{{ route('admin.events.reject', $event) }}">
                                            @csrf
                                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Причина отклонения</label>
                                            <textarea name="reason" rows="3" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300 resize-none">Не соответствует правилам площадки.</textarea>
                                            <div class="flex gap-2 mt-3">
                                                <button type="submit" class="flex-1 bg-red-500 hover:bg-red-600 text-white rounded-xl py-2 text-sm font-semibold">Отклонить</button>
                                                <button type="button" @click="open=false" class="flex-1 border border-gray-200 text-gray-500 rounded-xl py-2 text-sm hover:bg-gray-50">Отмена</button>
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

    {{-- ОПУБЛИКОВАННЫЕ --}}
    @if($tab === 'approved')
        <div class="space-y-3">
            @forelse($approved as $event)
                <div class="bg-white rounded-2xl border border-gray-200 p-4 flex items-center gap-4">
                    {{-- Обложка --}}
                    @if($event->photos->first())
                        <div class="w-14 h-14 rounded-xl overflow-hidden flex-shrink-0">
                            <img src="{{ Storage::url($event->photos->first()->path) }}" class="w-full h-full object-cover" alt="">
                        </div>
                    @else
                        <div class="w-1 self-stretch rounded-full bg-emerald-400 flex-shrink-0"></div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-sm truncate">{{ $event->name }}</p>
                        <p class="text-xs text-gray-400">{{ $event->category->name }} · {{ \Carbon\Carbon::parse($event->event_date)->translatedFormat('d M Y') }} · {{ $event->user->name }}</p>
                        @if($event->socials->count())
                            <div class="flex gap-1 mt-1">
                                @foreach($event->socials as $s)
                                    <span class="text-[10px] bg-gray-100 rounded px-1.5 py-0.5 text-gray-500">{{ $s->platform }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <form method="POST" action="{{ route('admin.events.reject', $event) }}">
                        @csrf
                        <input type="hidden" name="reason" value="Снято с публикации администратором.">
                        <button type="submit" class="text-xs text-red-400 border border-red-100 rounded-lg px-3 py-1.5 hover:bg-red-50">Снять</button>
                    </form>
                    <a href="{{ route('events.show', $event) }}" class="text-xs text-[#4A40E0] border border-indigo-200 rounded-lg px-3 py-1.5 hover:bg-indigo-50">Открыть</a>
                </div>
            @empty
                <div class="text-center py-16 text-gray-400">Нет опубликованных событий</div>
            @endforelse
            {{ $approved->links() }}
        </div>
    @endif

    {{-- ОТКЛОНЁННЫЕ --}}
    @if($tab === 'rejected')
        <div class="space-y-3">
            @forelse($rejected as $event)
                <div class="bg-white rounded-2xl border border-gray-200 p-4 flex items-center gap-4">
                    <div class="w-1 self-stretch rounded-full bg-red-400 flex-shrink-0"></div>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-sm truncate text-gray-500">{{ $event->name }}</p>
                        <p class="text-xs text-gray-400">{{ $event->category->name }} · {{ $event->user->name }} · {{ $event->updated_at->diffForHumans() }}</p>
                    </div>
                    <form method="POST" action="{{ route('admin.events.approve', $event) }}">
                        @csrf
                        <button type="submit" class="text-xs text-emerald-600 border border-emerald-200 rounded-lg px-3 py-1.5 hover:bg-emerald-50">Одобрить</button>
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
