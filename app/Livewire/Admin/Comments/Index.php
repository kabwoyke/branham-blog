<?php

namespace App\Livewire\Admin\Comments;

use App\Models\Comment;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
#[Title('Comments')]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $filter = 'pending';

    public function updatedFilter(): void
    {
        $this->resetPage();
    }

    public function approve(int $commentId): void
    {
        Comment::findOrFail($commentId)->update(['approved_at' => now()]);
    }

    public function unapprove(int $commentId): void
    {
        Comment::findOrFail($commentId)->update(['approved_at' => null]);
    }

    public function delete(int $commentId): void
    {
        Comment::findOrFail($commentId)->delete();

        session()->flash('status', 'Comment deleted.');
    }

    public function render(): View
    {
        return view('livewire.admin.comments.index', [
            'comments' => Comment::query()
                ->with('article')
                ->when($this->filter === 'pending', fn ($query) => $query->pending())
                ->when($this->filter === 'approved', fn ($query) => $query->approved())
                ->latest()
                ->paginate(20),
        ]);
    }
}
