@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="flex flex-wrap items-center justify-center gap-2 text-sm font-semibold">
        @if ($paginator->onFirstPage())
            <span class="rounded-lg px-4 py-2 text-muted/60" aria-disabled="true">&larr; Newer</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="rounded-lg border border-muted/30 px-4 py-2 hover:border-brand hover:text-brand">&larr; Newer</a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="px-2 text-muted" aria-hidden="true">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="rounded-lg bg-brand px-4 py-2 text-white" aria-current="page">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="rounded-lg border border-muted/30 px-4 py-2 hover:border-brand hover:text-brand" aria-label="Go to page {{ $page }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="rounded-lg border border-muted/30 px-4 py-2 hover:border-brand hover:text-brand">Older &rarr;</a>
        @else
            <span class="rounded-lg px-4 py-2 text-muted/60" aria-disabled="true">Older &rarr;</span>
        @endif
    </nav>
@endif
