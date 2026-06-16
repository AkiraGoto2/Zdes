<x-app-layout>
<div class="max-w-xl mx-auto px-4 py-10">

    @if(session('success'))
        <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-2xl px-5 py-4 text-sm flex items-center gap-2">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">

        
        <div class="p-6 pb-0">
            <div class="flex items-center justify-between mb-1">
                <span class="text-xs font-medium text-[#4A40E0] bg-indigo-50 rounded-full px-3 py-1">
                    {{ $ticket->event->category->name }}
                </span>
                <span class="text-xs px-3 py-1 rounded-full
                    {{ $ticket->status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-600' }}">
                    {{ $ticket->status === 'active' ? '✓ Активен' : 'Отменён' }}
                </span>
            </div>
            <h1 class="text-xl font-bold mt-3">{{ $ticket->event->name }}</h1>
            <p class="text-sm text-gray-500 mt-1">
                {{ \Carbon\Carbon::parse($ticket->event->event_date)->translatedFormat('d F Y, H:i') }}
            </p>
            <p class="text-sm text-gray-500">{{ $ticket->event->address }}</p>
        </div>

        
        <div class="mx-6 my-5 border-t-2 border-dashed border-gray-200"></div>

        
        <div class="px-6 pb-2 text-center">
            <p class="text-xs text-gray-400 uppercase tracking-widest mb-2">Код билета</p>
            <p class="text-3xl font-black tracking-[6px] text-[#4A40E0] font-mono">{{ $ticket->ticket_code }}</p>
            <p class="text-xs text-gray-400 mt-1">предъявите на входе</p>
        </div>

        <div class="mx-6 my-5 border-t-2 border-dashed border-gray-200"></div>

        
        <div class="px-6 pb-6 space-y-3 text-sm">
            <div class="flex justify-between">
                <span class="text-gray-500">Владелец</span>
                <span class="font-medium">{{ $ticket->buyer_name }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Email</span>
                <span class="font-medium">{{ $ticket->buyer_email }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Количество</span>
                <span class="font-medium">{{ $ticket->quantity }} шт.</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Уплачено</span>
                <span class="font-semibold {{ $ticket->price_paid == 0 ? 'text-emerald-600' : '' }}">
                    {{ $ticket->price_paid == 0 ? 'Бесплатно' : number_format($ticket->price_paid * $ticket->quantity, 0, '', ' ') . ' ₽' }}
                </span>
            </div>
        </div>
    </div>

    <div class="mt-4 flex gap-3">
        <a href="{{ route('events.show', $ticket->event) }}"
           class="flex-1 text-center border border-gray-200 rounded-xl py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-50 transition">
            К событию
        </a>
        <a href="{{ route('dashboard') }}"
           class="flex-1 text-center bg-[#4A40E0] text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-[#3d35c7] transition">
            Мои билеты
        </a>
    </div>
    @if($ticket->status === 'active')
    <div class="mt-3">
        <form method="POST" action="{{ route('tickets.cancel', $ticket) }}">
            @csrf @method('PATCH')
            <button type="button" onclick="document.getElementById('cancel-ticket-modal').style.display='flex'"
                class="w-full text-center text-sm text-red-500 hover:text-red-700 py-2 transition">
                Отменить билет
            </button>
        </form>
    </div>

    <div id="cancel-ticket-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center;">
        <div style="background:white;border-radius:20px;padding:32px;max-width:380px;width:90%;text-align:center;">
            <h3 style="font-size:18px;font-weight:700;margin-bottom:8px;">Отменить билет?</h3>
            <p style="font-size:13px;color:#6b7280;margin-bottom:24px;">Билет будет помечен как отменённый. Это действие нельзя отменить.</p>
            <div style="display:flex;gap:12px;">
                <button onclick="document.getElementById('cancel-ticket-modal').style.display='none'"
                    style="flex:1;padding:10px 0;border:1.5px solid #e5e7eb;border-radius:12px;font-size:14px;font-weight:600;color:#374151;background:white;cursor:pointer;">
                    Назад
                </button>
                <form method="POST" action="{{ route('tickets.cancel', $ticket) }}" style="flex:1;">
                    @csrf @method('PATCH')
                    <button type="submit"
                        style="width:100%;padding:10px 0;background:#ef4444;border:none;border-radius:12px;font-size:14px;font-weight:600;color:white;cursor:pointer;">
                        Отменить
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
</x-app-layout>
