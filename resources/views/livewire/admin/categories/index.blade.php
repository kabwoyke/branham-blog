<div wire:poll.10s.visible="checkForChanges">
    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold">Categories</h1>
            <p class="mt-2 text-sm text-muted">Add, rename or remove categories. A category's address is set when it is created and does not change on rename. A category can only be deleted when it has no articles.</p>
        </div>
        <button type="button" wire:click="create" class="shrink-0 rounded-lg bg-brand px-4 py-2 shadow-sm text-sm font-medium text-white hover:bg-brand/90">New category</button>
    </div>

    <ul class="mt-6 divide-y divide-muted/20 card">
        @if ($creating)
            <li class="px-4 py-4">
                <form wire:submit="save" class="space-y-4">
                    <x-admin.input label="Name" name="name" wire:model="name" required />
                    <x-admin.textarea label="Description" name="description" wire:model="description" rows="2" />
                    <div class="flex gap-3 text-sm">
                        <button type="submit" class="rounded-lg bg-brand px-4 py-2 shadow-sm font-medium text-white hover:bg-brand/90">Add category</button>
                        <button type="button" wire:click="cancel" class="text-muted hover:text-brand">Cancel</button>
                    </div>
                </form>
            </li>
        @endif
        @foreach ($categories as $category)
            <li class="px-4 py-4" wire:key="category-{{ $category->id }}">
                @if ($editingId === $category->id)
                    <form wire:submit="save" class="space-y-4">
                        <x-admin.input label="Name" name="name" wire:model="name" required />
                        <x-admin.textarea label="Description" name="description" wire:model="description" rows="2" />
                        <div class="flex gap-3 text-sm">
                            <button type="submit" class="rounded-lg bg-brand px-4 py-2 shadow-sm font-medium text-white hover:bg-brand/90">Save</button>
                            <button type="button" wire:click="cancel" class="text-muted hover:text-brand">Cancel</button>
                        </div>
                    </form>
                @else
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="font-medium">{{ $category->name }} <span class="text-sm font-normal text-muted">/{{ $category->slug }} · {{ $category->articles_count }} {{ Str::plural('article', $category->articles_count) }}</span></p>
                            <p class="mt-1 text-sm text-muted">{{ $category->description }}</p>
                        </div>
                        <div class="flex shrink-0 gap-4 text-sm">
                            <button type="button" wire:click="edit({{ $category->id }})" class="text-brand hover:underline">Edit</button>
                            <button type="button" wire:click="delete({{ $category->id }})" wire:confirm="Delete the category '{{ $category->name }}'?" class="text-red-700 hover:underline">Delete</button>
                        </div>
                    </div>
                @endif
            </li>
        @endforeach
    </ul>
</div>
