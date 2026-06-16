@if ($paginator->hasPages())
<nav class="flex items-center justify-center gap-1">

    @if ($paginator->onFirstPage())
        <span class="w-9 h-9 flex items-center justify-center rounded-xl text-gray-300 border border-gray-100 cursor-not-allowed text-sm">←</span>
    @else
        <a href="{{ $paginator->previousPageUrl() }}" class="w-9 h-9 flex items-center justify-center rounded-xl text-gray-500 border border-gray-200 hover:bg-indigo-50 hover:text-[#4A40E0] hover:border-indigo-200 transition text-sm">←</a>
    @endif

    @foreach ($elements as $element)
        @if (is_string($element))
            <span class="w-9 h-9 flex items-center justify-center rounded-xl text-gray-400 text-sm">…</span>
        @endif

        @if (is_array($element))
            @foreach ($element as $page => $url)
                @if ($page == $paginator->currentPage())
                    <span class="w-9 h-9 flex items-center justify-center rounded-xl bg-[#4A40E0] text-white text-sm font-semibold shadow-sm">{{ $page }}</span>
                @else
                    <a href="{{ $url }}" class="w-9 h-9 flex items-center justify-center rounded-xl text-gray-600 border border-gray-200 hover:bg-indigo-50 hover:text-[#4A40E0] hover:border-indigo-200 transition text-sm">{{ $page }}</a>
                @endif
            @endforeach
        @endif
    @endforeach

    @if ($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}" class="w-9 h-9 flex items-center justify-center rounded-xl text-gray-500 border border-gray-200 hover:bg-indigo-50 hover:text-[#4A40E0] hover:border-indigo-200 transition text-sm">→</a>
    @else
        <span class="w-9 h-9 flex items-center justify-center rounded-xl text-gray-300 border border-gray-100 cursor-not-allowed text-sm">→</span>
    @endif

</nav>
@endif
