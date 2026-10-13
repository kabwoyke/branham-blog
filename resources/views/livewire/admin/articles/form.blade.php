<div>
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-semibold">{{ $article ? 'Edit article' : 'New article' }}</h1>
        <a href="{{ route('admin.articles.index') }}" class="text-sm text-muted hover:text-brand">&larr; All articles</a>
    </div>

    <form wire:submit="save" class="mt-8 grid gap-8 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <x-admin.input label="Title" name="title" wire:model.live.debounce.400ms="title" required />
            <x-admin.input label="Slug" name="slug" wire:model.blur="slug" hint="Used in the article address. Generated from the title; edit if you wish." required />
            <x-admin.textarea label="Excerpt" name="excerpt" wire:model="excerpt" rows="3" hint="One or two sentences shown in listings and when shared." required />
            <x-admin.markdown-editor label="Body" model="body" />
        </div>

        <aside class="space-y-6">
            <section class="space-y-4 border border-muted/20 bg-white p-4" aria-labelledby="publish-heading">
                <h2 id="publish-heading" class="font-sans text-sm font-semibold uppercase tracking-wide">Publish</h2>

                <x-admin.select label="Status" name="status" wire:model.live="status">
                    @foreach ($statuses as $statusOption)
                        <option value="{{ $statusOption->value }}">{{ $statusOption->label() }}</option>
                    @endforeach
                </x-admin.select>

                @if ($status === 'scheduled')
                    <x-admin.input label="Publish on" name="published_at" type="datetime-local" wire:model="published_at" hint="Server time ({{ config('app.timezone') }})." />
                @endif

                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" wire:model="is_featured" class="size-4 accent-brand">
                    Featured article
                </label>

                <button type="submit" class="w-full rounded-lg bg-brand px-4 py-3 text-sm font-medium text-white hover:bg-brand/90" wire:loading.attr="disabled" wire:target="save,image">
                    {{ $status === 'published' ? 'Save and publish' : ($status === 'scheduled' ? 'Schedule' : 'Save draft') }}
                </button>
            </section>

            <section class="space-y-4 border border-muted/20 bg-white p-4" aria-labelledby="details-heading">
                <h2 id="details-heading" class="font-sans text-sm font-semibold uppercase tracking-wide">Details</h2>

                <x-admin.select label="Category" name="category_id" wire:model="category_id">
                    @foreach ($categories as $categoryOption)
                        <option value="{{ $categoryOption->id }}">{{ $categoryOption->name }}</option>
                    @endforeach
                </x-admin.select>

                <x-admin.select label="Type" name="type" wire:model="type">
                    @foreach ($types as $typeOption)
                        <option value="{{ $typeOption->value }}">{{ $typeOption->label() }}</option>
                    @endforeach
                </x-admin.select>
            </section>

            <section class="space-y-4 border border-muted/20 bg-white p-4" aria-labelledby="image-heading">
                <h2 id="image-heading" class="font-sans text-sm font-semibold uppercase tracking-wide">Featured image</h2>

                @if ($image)
                    <img src="{{ $image->temporaryUrl() }}" alt="Preview of the selected image" class="w-full">
                @elseif ($article?->featured_image && ! $removeImage)
                    <img src="{{ $article->featured_image_url }}" alt="{{ $article->featured_image_alt }}" class="w-full">
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" wire:model.live="removeImage" class="size-4 accent-brand">
                        Remove image
                    </label>
                @endif

                <x-admin.input label="Upload image" name="image" type="file" wire:model="image" accept="image/jpeg,image/png,image/webp" hint="JPG, PNG or WebP, up to 5 MB. Resized to 1600 px wide." />
                <x-admin.input label="Alt text" name="featured_image_alt" wire:model="featured_image_alt" hint="Describe the image for people using screen readers. Required when there is an image." />
            </section>

            <section class="space-y-4 border border-muted/20 bg-white p-4" aria-labelledby="seo-heading">
                <h2 id="seo-heading" class="font-sans text-sm font-semibold uppercase tracking-wide">SEO</h2>
                <x-admin.input label="Meta title" name="meta_title" wire:model="meta_title" hint="Optional. Up to 70 characters. Defaults to the title." />
                <x-admin.textarea label="Meta description" name="meta_description" wire:model="meta_description" rows="3" hint="Optional. Up to 160 characters. Defaults to the excerpt." />
            </section>
        </aside>
    </form>
</div>
