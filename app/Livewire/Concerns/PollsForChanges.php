<?php

namespace App\Livewire\Concerns;

use App\Support\LiveUpdates;
use Livewire\Attributes\Locked;

/**
 * Lets a component poll cheaply: `wire:poll.10s.visible="checkForChanges"`.
 *
 * Each poll reads one cache entry. When none of the component's tables changed since it last
 * rendered, the request skips rendering entirely (no queries, no HTML sent back).
 */
trait PollsForChanges
{
    #[Locked]
    public string $liveStamp = '';

    /**
     * The tables this component shows, e.g. ['articles', 'categories'].
     *
     * @return list<string>
     */
    abstract protected function liveTopics(): array;

    public function checkForChanges(): void
    {
        if (LiveUpdates::stamp($this->liveTopics()) === $this->liveStamp) {
            $this->skipRender();

            return;
        }

        $this->changesDetected();
    }

    /**
     * Called when a poll finds new data, before re-rendering. Override to react differently.
     */
    protected function changesDetected(): void {}

    public function renderingPollsForChanges(): void
    {
        $this->liveStamp = LiveUpdates::stamp($this->liveTopics());
    }
}
