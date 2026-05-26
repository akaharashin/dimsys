@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center gap-2">

        @if ($paginator->onFirstPage())
            <span class="inline-flex items-center px-4 py-1.5 text-sm font-medium text-gray-300 bg-white border border-gray-200 cursor-not-allowed rounded-lg">
                {!! __('pagination.previous') !!}
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                class="inline-flex items-center px-4 py-1.5 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-red-50 hover:text-[#A51616] transition-colors">
                {!! __('pagination.previous') !!}
            </a>
        @endif

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next"
                class="inline-flex items-center px-4 py-1.5 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-red-50 hover:text-[#A51616] transition-colors">
                {!! __('pagination.next') !!}
            </a>
        @else
            <span class="inline-flex items-center px-4 py-1.5 text-sm font-medium text-gray-300 bg-white border border-gray-200 cursor-not-allowed rounded-lg">
                {!! __('pagination.next') !!}
            </span>
        @endif

    </nav>
@endif
