<?php

namespace App\Livewire\Admin\Messages;

use App\Livewire\Concerns\PollsForChanges;
use App\Models\ContactMessage;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
#[Title('Messages')]
class Index extends Component
{
    use PollsForChanges, WithPagination;

    #[Url]
    public bool $unreadOnly = false;

    public ?int $openId = null;

    public function updatedUnreadOnly(): void
    {
        $this->resetPage();
    }

    public function open(int $messageId): void
    {
        $message = ContactMessage::findOrFail($messageId);

        if (! $message->isRead()) {
            $message->update(['read_at' => now()]);
        }

        $this->openId = $this->openId === $message->id ? null : $message->id;
    }

    public function markRead(int $messageId): void
    {
        ContactMessage::findOrFail($messageId)->update(['read_at' => now()]);
    }

    public function markUnread(int $messageId): void
    {
        ContactMessage::findOrFail($messageId)->update(['read_at' => null]);
    }

    public function delete(int $messageId): void
    {
        ContactMessage::findOrFail($messageId)->delete();
        $this->openId = null;

        $this->dispatch('notify', type: 'status', message: 'Message deleted.');
    }

    /**
     * @return list<string>
     */
    protected function liveTopics(): array
    {
        return ['contact_messages'];
    }

    public function render(): View
    {
        return view('livewire.admin.messages.index', [
            'messages' => ContactMessage::query()
                ->when($this->unreadOnly, fn ($query) => $query->unread())
                ->latest()
                ->paginate(20),
        ]);
    }
}
