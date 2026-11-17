<?php

namespace App\Livewire\Admin\Categories;

use App\Livewire\Concerns\PollsForChanges;
use App\Models\Category;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Categories')]
class Index extends Component
{
    use PollsForChanges;

    public ?int $editingId = null;

    public bool $creating = false;

    public string $name = '';

    public string $description = '';

    public function create(): void
    {
        $this->cancel();
        $this->creating = true;
    }

    public function edit(int $categoryId): void
    {
        $this->cancel();
        $category = Category::findOrFail($categoryId);

        $this->editingId = $category->id;
        $this->name = $category->name;
        $this->description = (string) $category->description;
        $this->resetValidation();
    }

    public function cancel(): void
    {
        $this->reset('editingId', 'creating', 'name', 'description');
        $this->resetValidation();
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $attributes = [
            'name' => $validated['name'],
            'description' => $validated['description'] ?: null,
        ];

        if ($this->creating) {
            Category::create($attributes + ['slug' => $this->uniqueSlug($validated['name'])]);
            $message = 'Category added.';
        } else {
            Category::findOrFail($this->editingId)->update($attributes);
            $message = 'Category updated.';
        }

        $this->cancel();
        $this->dispatch('notify', type: 'status', message: $message);
    }

    public function delete(int $categoryId): void
    {
        $category = Category::findOrFail($categoryId);

        if ($category->articles()->withTrashed()->exists()) {
            $this->dispatch('notify', type: 'error', message: 'This category still has articles (including trashed ones). Move or delete them first.');

            return;
        }

        $category->delete();

        if ($this->editingId === $categoryId) {
            $this->cancel();
        }

        $this->dispatch('notify', type: 'status', message: 'Category deleted.');
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'category';
        $slug = $base;
        $suffix = 2;

        while (Category::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    /**
     * @return list<string>
     */
    protected function liveTopics(): array
    {
        return ['categories', 'articles'];
    }

    public function render(): View
    {
        return view('livewire.admin.categories.index', [
            'categories' => Category::withCount('articles')->orderBy('id')->get(),
        ]);
    }
}
