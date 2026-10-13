<div>
    <h1 class="text-3xl font-semibold">Categories</h1>
    <p class="mt-2 text-sm text-muted">The five categories are fixed. You can edit their names and descriptions; addresses stay the same.</p>

    <ul class="mt-6 divide-y divide-muted/20 border border-muted/20 bg-white">
        @foreach ($categories as $category)
            <li class="px-4 py-4" wire:key="category-{{ $category->id }}">
                @if ($editingId === $category->id)
                    <form wire:submit="save" class="space-y-4">
                        <x-admin.input label="Name" name="name" wire:model="name" required />
                        <x-admin.textarea label="Description" name="description" wire:model="description" rows="2" />
                        <div class="flex gap-3 text-sm">
                            <button type="submit" class="rounded-lg bg-brand px-4 py-2 font-medium text-white hover:bg-brand/90">Save</button>
                            <button type="button" wire:click="cancel" class="text-muted hover:text-brand">Cancel</button>
                        </div>
                    </form>
                @else
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="font-medium">{{ $category->name }} <span class="text-sm font-normal text-muted">/{{ $category->slug }} · {{ $category->articles_count }} {{ Str::plural('article', $category->articles_count) }}</span></p>
                            <p class="mt-1 text-sm text-muted">{{ $category->description }}</p>
                        </div>
                        <button type="button" wire:click="edit({{ $category->id }})" class="text-sm text-brand hover:underline">Edit</button>
                    </div>
                @endif
            </li>
        @endforeach
    </ul>
</div>
