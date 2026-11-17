<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\PollsForChanges;
use App\Models\Comment;
use App\Models\ContactMessage;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * The "new items" count beside a sidebar link, kept up to date by polling.
 */
class NavBadge extends Component
{
    use PollsForChanges;

    public const SOURCES = [
        'messages' => 'contact_messages',
        'comments' => 'comments',
    ];

    /** One of the keys of SOURCES. */
    #[Locked]
    public string $source;

    #[Locked]
    public bool $active = false;

    /**
     * @return list<string>
     */
    protected function liveTopics(): array
    {
        return [self::SOURCES[$this->source] ?? $this->source];
    }

    public function render(): View
    {
        $count = match ($this->source) {
            'messages' => ContactMessage::unread()->count(),
            'comments' => Comment::pending()->count(),
            default => 0,
        };

        return view('livewire.admin.nav-badge', ['count' => $count]);
    }
}
