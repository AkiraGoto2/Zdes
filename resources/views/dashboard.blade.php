<x-app-layout>
<div class="max-w-[1100px] mx-auto px-6 py-8">

    {{-- Flash --}}
    @if(session('success'))
        <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl px-4 py-3 text-sm flex items-center gap-2">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Шапка профиля --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-6 mb-6 flex items-center gap-5">
        <div class="w-16 h-16 rounded-2xl bg-[#4A40E0] flex items-center justify-center text-white text-2xl font-bold flex-shrink-0">
            {{ mb_strtoupper(mb_substr(Auth::user()->name, 0, 1)) }}
        </div>
        <div class="flex-1">
            <h1 class="text-xl font-bold">{{ Auth::user()->name }} {{ Auth::user()->lastname }}</h1>
            <p class="text-sm text-gray-500">{{ Auth::user()->email }} · {{ Auth::user()->tel }}</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('events.create') }}" class="bg-[#4A40E0] text-white rounded-xl px-5 py-2.5 text-sm font-semibold hover:bg-[#3d35c7] transition-colors">+ Создать событие</a>
            <a href="{{ route('profile.edit') }}" class="border border-gray-200 text-gray-600 rounded-xl px-5 py-2.5 text-sm font-semibold hover:bg-gray-50 transition-colors">Настройки</a>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-6">

        {{-- Левая колонка: статистика + уведомления --}}
        <div class="space-y-4">

            <!-- {{-- Статы --}}
            <div class="grid grid-cols-2 gap-3">
                <div class="bg-white rounded-2xl border border-gray-200 p-4 text-center">
                    <div class="text-2xl font-bold text-[#4A40E0]">{{ $myEvents->count() }}</div>
                    <div class="text-xs text-gray-500 mt-0.5">Создано событий</div>
                </div>
                <div class="bg-white rounded-2xl border border-gray-200 p-4 text-center">
                    <div class="text-2xl font-bold text-emerald-600">{{ $applications->count() }}</div>
                    <div class="text-xs text-gray-500 mt-0.5">Записей</div>
                </div>
                <div class="bg-white rounded-2xl border border-gray-200 p-4 text-center">
                    <div class="text-2xl font-bold text-amber-500">{{ $myEvents->where('status','pending')->count() }}</div>
                    <div class="text-xs text-gray-500 mt-0.5">На проверке</div>
                </div>
                <div class="bg-white rounded-2xl border border-gray-200 p-4 text-center">
                    <div class="text-2xl font-bold text-gray-700">{{ $myEvents->where('status','approved')->count() }}</div>
                    <div class="text-xs text-gray-500 mt-0.5">Опубликовано</div>
                </div>
            </div> -->

            {{-- Уведомления --}}
            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-semibold">Уведомления</span>
                        @if($unreadCount > 0)
                            <span class="bg-[#4A40E0] text-white text-[10px] font-bold rounded-full px-1.5 py-0.5">{{ $unreadCount }}</span>
                        @endif
                    </div>
                    @if($unreadCount > 0)
                        <form method="POST" action="{{ route('notifications.read-all') }}">
                            @csrf
                            <button class="text-xs text-[#4A40E0] hover:underline">Прочитать все</button>
                        </form>
                    @endif
                </div>

                @if($notifications->isEmpty())
                    <div class="px-4 py-6 text-center text-xs text-gray-400">Нет уведомлений</div>
                @else
                    <div class="divide-y divide-gray-50 max-h-72 overflow-y-auto">
                        @foreach($notifications->take(10) as $notif)
                            <div class="px-4 py-3 flex gap-3 {{ $notif->is_read ? 'opacity-60' : 'bg-indigo-50/40' }}">
                                <div class="mt-0.5 flex-shrink-0">
                                    @if($notif->type === 'event_approved')
                                        <div class="w-6 h-6 rounded-full bg-emerald-100 flex items-center justify-center">
                                            <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        </div>
                                    @elseif($notif->type === 'event_rejected')
                                        <div class="w-6 h-6 rounded-full bg-red-100 flex items-center justify-center">
                                            <svg class="w-3 h-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </div>
                                    @else
                                        <div class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center">
                                            <svg class="w-3 h-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs text-gray-700 leading-snug">{{ $notif->message }}</p>
                                    <p class="text-[10px] text-gray-400 mt-0.5">{{ $notif->created_at->diffForHumans() }}</p>
                                </div>
                                @if(!$notif->is_read)
                                    <form method="POST" action="{{ route('notifications.read', $notif->id) }}">
                                        @csrf
                                        <button class="text-gray-300 hover:text-gray-500 mt-0.5">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Правая колонка: мои события + записи --}}
        <div class="col-span-2 space-y-6">

            {{-- Мои события --}}
            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                    <span class="font-semibold">Мои события</span>
                    <a href="{{ route('my-events') }}" class="text-xs text-[#4A40E0] hover:underline">Все →</a>
                </div>
                @if($myEvents->isEmpty())
                    <div class="px-5 py-8 text-center text-sm text-gray-400">Вы ещё не создали ни одного события</div>
                @else
                    <div class="divide-y divide-gray-50">
                        @foreach($myEvents->take(5) as $event)
                            <div class="flex items-center gap-3 px-5 py-3">
                                <div class="w-1 self-stretch rounded-full flex-shrink-0
                                    {{ $event->status === 'approved' ? 'bg-emerald-400' : ($event->status === 'pending' ? 'bg-amber-400' : 'bg-red-400') }}">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium truncate">{{ $event->name }}</p>
                                    <p class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($event->event_date)->translatedFormat('d M Y') }} · {{ $event->category->name }}</p>
                                </div>
                                <span class="text-[11px] font-medium rounded-full px-2 py-0.5 flex-shrink-0
                                    {{ $event->status === 'approved' ? 'bg-emerald-50 text-emerald-700' : ($event->status === 'pending' ? 'bg-amber-50 text-amber-700' : 'bg-red-50 text-red-600') }}">
                                    {{ $event->status === 'approved' ? 'Опубликовано' : ($event->status === 'pending' ? 'На проверке' : 'Отклонено') }}
                                </span>
                                @if($event->status === 'approved')
                                    <a href="{{ route('events.show', $event) }}" class="text-xs text-gray-400 hover:text-[#4A40E0]">→</a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Мои записи --}}
            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <span class="font-semibold">Мои записи на события</span>
                </div>
                @if($applications->isEmpty())
                    <div class="px-5 py-8 text-center text-sm text-gray-400">Вы ещё не записывались ни на одно событие</div>
                @else
                    <div class="divide-y divide-gray-50">
                        @foreach($applications as $app)
                            <div class="flex items-center gap-4 px-5 py-3">
                                <div class="w-9 h-9 rounded-xl bg-indigo-50 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-[#4A40E0]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium truncate">{{ $app->event->name }}</p>
                                    <p class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($app->event->event_date)->translatedFormat('d M Y, H:i') }} · {{ $app->event->address }}</p>
                                </div>
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    <a href="{{ route('events.show', $app->event) }}" class="text-xs text-[#4A40E0] border border-indigo-200 rounded-lg px-3 py-1.5 hover:bg-indigo-50 transition-colors">Открыть</a>
                                    <form method="POST" action="{{ route('events.unapply', $app->event) }}" onsubmit="return confirm('Отменить запись?')">
                                        @csrf @method('DELETE')
                                        <button class="text-xs text-red-500 border border-red-100 rounded-lg px-3 py-1.5 hover:bg-red-50 transition-colors">Отменить</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>
</x-app-layout>
