<x-app-layout>

<div class="max-w-[1280px] mx-auto px-6 py-8">

    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold">Мои события</h1>
            <p class="text-sm text-gray-500 mt-0.5">Управляйте своими мероприятиями</p>
        </div>
        <a href="{{ route('events.create') }}"
           class="bg-[#4A40E0] text-white rounded-xl text-sm py-2.5 px-5 font-semibold hover:bg-[#3d35c7] transition-colors">
            + Создать событие
        </a>
    </div>

    {{-- Flash сообщение --}}
    @if(session('success'))
        <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl px-4 py-3 text-sm flex items-center gap-2">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if($events->isEmpty())
        <div class="text-center py-24 text-gray-400">
            <svg class="w-12 h-12 mx-auto mb-4 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4"/>
            </svg>
            <p class="text-lg font-medium text-gray-500">У вас пока нет событий</p>
            <p class="text-sm mt-1">Создайте первое — оно появится на карте после проверки</p>
            <a href="{{ route('events.create') }}"
               class="inline-block mt-4 bg-[#4A40E0] text-white rounded-xl text-sm py-2.5 px-6 font-semibold hover:bg-[#3d35c7] transition-colors">
                Создать событие
            </a>
        </div>
    @else
        <div class="space-y-3">
            @foreach($events as $event)
                <div class="bg-white rounded-2xl border border-gray-200 p-4 flex items-center gap-4 hover:shadow-sm transition-shadow">

                    {{-- Статус-полоска --}}
                    <div class="w-1 self-stretch rounded-full flex-shrink-0
                        {{ $event->status === 'approved' ? 'bg-emerald-400' : '' }}
                        {{ $event->status === 'pending'  ? 'bg-amber-400' : '' }}
                        {{ $event->status === 'rejected' ? 'bg-red-400' : '' }}">
                    </div>

                    {{-- Инфо --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-0.5">
                            <h3 class="font-semibold text-[15px] truncate">{{ $event->name }}</h3>
                            {{-- Бейдж статуса --}}
                            <span class="flex-shrink-0 text-[11px] font-medium rounded-full px-2 py-0.5
                                {{ $event->status === 'approved' ? 'bg-emerald-50 text-emerald-700' : '' }}
                                {{ $event->status === 'pending'  ? 'bg-amber-50 text-amber-700' : '' }}
                                {{ $event->status === 'rejected' ? 'bg-red-50 text-red-600' : '' }}">
                                {{ $event->status === 'approved' ? 'Опубликовано' : ($event->status === 'pending' ? 'На проверке' : 'Отклонено') }}
                            </span>
                        </div>
                        <div class="flex items-center gap-3 text-xs text-gray-400">
                            <span>{{ $event->category->name }}</span>
                            <span>·</span>
                            <span>{{ \Carbon\Carbon::parse($event->event_date)->translatedFormat('d M Y, H:i') }}</span>
                            <span>·</span>
                            <span>{{ $event->address }}</span>
                        </div>
                    </div>

                    {{-- Действия --}}
                    <div class="flex items-center gap-2 flex-shrink-0">
                        @if($event->status === 'approved')
                            <a href="{{ route('events.show', $event) }}"
                               class="text-xs text-[#4A40E0] border border-indigo-200 rounded-lg px-3 py-1.5 hover:bg-indigo-50 transition-colors">
                                Смотреть
                            </a>
                        @endif
                        <a href="{{ route('events.edit', $event) }}"
                           class="text-xs text-gray-600 border border-gray-200 rounded-lg px-3 py-1.5 hover:bg-gray-50 transition-colors">
                            Изменить
                        </a>
                        <form method="POST" action="{{ route('events.destroy', $event) }}" onsubmit="return confirm('Удалить «{{ addslashes($event->name) }}»?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs text-red-500 border border-red-100 rounded-lg px-3 py-1.5 hover:bg-red-50 transition-colors">
                                Удалить
                            </button>
                        </form>
                    </div>

                </div>
            @endforeach
        </div>
    @endif

</div>

</x-app-layout>
