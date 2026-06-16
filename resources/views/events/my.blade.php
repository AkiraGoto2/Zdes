<x-app-layout>

<div class="max-w-[1280px] mx-auto px-4 sm:px-6 py-6 sm:py-8">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold">Мои события</h1>
            <p class="text-sm text-gray-500 mt-0.5">Управляйте своими мероприятиями</p>
        </div>
        <a href="{{ route('events.create') }}"
           class="bg-[#4A40E0] text-white rounded-xl text-sm py-2 px-4 sm:px-5 font-semibold hover:bg-[#3d35c7] transition-colors whitespace-nowrap">
            + Создать
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl px-4 py-3 text-sm flex items-center gap-2">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if($events->isEmpty())
        <div class="text-center py-20 text-gray-400">
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
                <div class="bg-white rounded-2xl border border-gray-200 p-4 hover:shadow-sm transition-shadow">
                    <div class="flex gap-3">

                        {{-- Цветная полоска статуса --}}
                        <div class="w-1 self-stretch rounded-full flex-shrink-0
                            {{ $event->status === 'approved' ? 'bg-emerald-400' : '' }}
                            {{ $event->status === 'pending'  ? 'bg-amber-400' : '' }}
                            {{ $event->status === 'rejected' ? 'bg-red-400' : '' }}">
                        </div>

                        {{-- Контент --}}
                        <div class="flex-1 min-w-0">

                            {{-- Название + статус --}}
                            <div class="flex items-start justify-between gap-2 mb-1">
                                <h3 class="font-semibold text-[15px] leading-tight">{{ $event->name }}</h3>
                                <span class="flex-shrink-0 text-[11px] font-medium rounded-full px-2 py-0.5
                                    {{ $event->status === 'approved' ? 'bg-emerald-50 text-emerald-700' : '' }}
                                    {{ $event->status === 'pending'  ? 'bg-amber-50 text-amber-700' : '' }}
                                    {{ $event->status === 'rejected' ? 'bg-red-50 text-red-600' : '' }}">
                                    {{ $event->status === 'approved' ? 'Опубликовано' : ($event->status === 'pending' ? 'На проверке' : 'Отклонено') }}
                                </span>
                            </div>

                            {{-- Мета --}}
                            <div class="text-xs text-gray-400 space-y-0.5 mb-3">
                                <div class="flex flex-wrap gap-x-2">
                                    <span>{{ $event->category->name }}</span>
                                    <span>·</span>
                                    <span>{{ \Carbon\Carbon::parse($event->event_date)->translatedFormat('d M Y, H:i') }}</span>
                                </div>
                                <div class="truncate">{{ $event->address }}</div>
                            </div>

                            {{-- Кнопки --}}
                            <div class="flex flex-wrap gap-2">
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
                                <button type="button"
                                    onclick="openDelModal('{{ route('events.destroy', $event) }}','{{ addslashes($event->name) }}')"
                                    class="text-xs text-red-500 border border-red-100 rounded-lg px-3 py-1.5 hover:bg-red-50 transition-colors">
                                    Удалить
                                </button>
                            </div>

                            {{-- Причина отклонения --}}
                            @if($event->status === 'rejected' && $event->rejection_reason)
                                <div class="mt-2 text-xs text-red-500 bg-red-50 rounded-lg px-3 py-2">
                                    {{ $event->rejection_reason }}
                                </div>
                            @endif

                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

</div>

<div id="del-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:white;border-radius:20px;padding:32px;max-width:400px;width:90%;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,.2);">
        <div style="width:52px;height:52px;background:#fee2e2;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
            <svg width="24" height="24" fill="none" stroke="#ef4444" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
        </div>
        <h3 style="font-size:18px;font-weight:700;margin-bottom:8px;">Удалить событие?</h3>
        <p id="del-modal-name" style="font-size:13px;color:#6b7280;margin-bottom:24px;"></p>
        <div style="display:flex;gap:12px;">
            <button onclick="closeDelModal()" style="flex:1;padding:10px 0;border:1.5px solid #e5e7eb;border-radius:12px;font-size:14px;font-weight:600;color:#374151;background:white;cursor:pointer;">Отмена</button>
            <form id="del-modal-form" method="POST" style="flex:1;">
                @csrf @method('DELETE')
                <button type="submit" style="width:100%;padding:10px 0;background:#ef4444;border:none;border-radius:12px;font-size:14px;font-weight:600;color:white;cursor:pointer;">Удалить</button>
            </form>
        </div>
    </div>
</div>
<script>
function openDelModal(action, name) {
    document.getElementById('del-modal-form').action = action;
    document.getElementById('del-modal-name').textContent = '«' + name + '» будет удалено навсегда.';
    document.getElementById('del-modal').style.display = 'flex';
}
function closeDelModal() { document.getElementById('del-modal').style.display = 'none'; }
document.getElementById('del-modal').addEventListener('click', e => { if(e.target===document.getElementById('del-modal')) closeDelModal(); });
</script>
</x-app-layout>
