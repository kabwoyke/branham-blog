<span wire:poll.20s="checkForChanges">
    @if ($count > 0)
        <span @class(['rounded-full px-2 py-0.5 text-xs font-bold', 'bg-brand text-white' => $active, 'bg-amber-400 text-ink' => ! $active])><span class="sr-only">{{ $count }} new</span><span aria-hidden="true">{{ $count }}</span></span>
    @endif
</span>
