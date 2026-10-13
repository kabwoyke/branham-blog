<?php

namespace App\Livewire\Admin\Subscribers;

use App\Models\Subscriber;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
#[Title('Subscribers')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function delete(int $subscriberId): void
    {
        Subscriber::findOrFail($subscriberId)->delete();

        session()->flash('status', 'Subscriber removed.');
    }

    public function render(): View
    {
        return view('livewire.admin.subscribers.index', [
            'subscribers' => Subscriber::query()
                ->when($this->search !== '', fn ($query) => $query->where('email', 'like', '%'.$this->search.'%'))
                ->latest()
                ->paginate(25),
            'confirmedCount' => Subscriber::confirmed()->count(),
        ]);
    }
}
