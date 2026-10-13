<?php

namespace App\Livewire\Admin\Categories;

use App\Models\Category;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Categories')]
class Index extends Component
{
    public ?int $editingId = null;

    public string $name = '';

    public string $description = '';

    public function edit(int $categoryId): void
    {
        $category = Category::findOrFail($categoryId);

        $this->editingId = $category->id;
        $this->name = $category->name;
        $this->description = (string) $category->description;
        $this->resetValidation();
    }

    public function cancel(): void
    {
        $this->reset('editingId', 'name', 'description');
        $this->resetValidation();
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        Category::findOrFail($this->editingId)->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?: null,
        ]);

        $this->cancel();
        session()->flash('status', 'Category updated.');
    }

    public function render(): View
    {
        return view('livewire.admin.categories.index', [
            'categories' => Category::withCount('articles')->orderBy('id')->get(),
        ]);
    }
}
