<div>
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold sm:text-3xl">{{ $article ? 'Edit article' : 'New article' }}</h1>
        <a href="{{ route('admin.articles.index') }}" wire:navigate class="text-sm text-muted hover:text-brand">&larr; All articles</a>
    </div>

    <form wire:submit="save" class="mt-8 grid gap-8 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <x-admin.input label="Title" name="title" wire:model.live.debounce.400ms="title" required />
            <x-admin.input label="Slug" name="slug" wire:model.blur="slug" hint="Used in the article address. Generated from the title; edit if you wish." required />
            <x-admin.textarea label="Excerpt" name="excerpt" wire:model="excerpt" rows="3" hint="One or two sentences shown in listings and when shared." required />
            <x-admin.markdown-editor label="Body" model="body" />

            <section class="space-y-4 card p-4" aria-labelledby="supporting-heading">
                <div>
                    <h2 id="supporting-heading" class="font-sans text-sm font-semibold uppercase tracking-wide">Supporting images</h2>
                    <p class="mt-1 text-xs text-muted">Upload an image or link to one that is already online, then choose where it goes: at the cursor in the body, or in any section of the article. The caption is optional.</p>
                </div>

                <div role="radiogroup" aria-label="Image source" class="inline-flex rounded-lg border border-muted/30 bg-white p-0.5 text-xs font-semibold">
                    @foreach (['upload' => 'Upload a file', 'link' => 'Link to an online image'] as $sourceValue => $sourceLabel)
                        <label @class(['cursor-pointer rounded-md px-3 py-1.5 transition-colors', 'bg-brand text-white' => $bodyImageSource === $sourceValue, 'text-muted hover:text-brand' => $bodyImageSource !== $sourceValue])>
                            <input type="radio" wire:model.live="bodyImageSource" value="{{ $sourceValue }}" class="sr-only">
                            {{ $sourceLabel }}
                        </label>
                    @endforeach
                </div>

                @if ($bodyImageSource === 'upload')
                    @if ($bodyImage)
                        <img src="{{ $bodyImage->temporaryUrl() }}" alt="Preview of the selected supporting image" class="max-h-48 rounded-lg">
                        <button type="button" wire:click="clearBodyImage" class="text-sm text-red-700 hover:underline">Remove selected image</button>
                    @endif

                    <x-admin.input label="Image" name="bodyImage" type="file" wire:key="body-image-{{ $imageInputVersion }}" wire:model="bodyImage" accept="image/jpeg,image/png,image/webp" hint="JPG, PNG or WebP, up to 5 MB. Resized to 1600 px wide." />
                @else
                    <div
                        x-data="{
                            state: 'empty',
                            get url() { return ($wire.bodyImageUrl || '').trim() },
                            get isWebAddress() { return /^https?:\/\/[^\s]+$/i.test(this.url) },
                            refresh() { this.state = this.url === '' ? 'empty' : (this.isWebAddress ? 'loading' : 'invalid') },
                        }"
                        x-init="refresh(); $watch('url', () => refresh())"
                    >
                        <x-admin.input label="Image address" name="bodyImageUrl" type="url" wire:model="bodyImageUrl" placeholder="https://example.com/photo.jpg" hint="Right-click an image online and choose “Copy image address”. Only link to images you have permission to use." />

                        <div x-cloak x-show="state !== 'empty'" class="mt-3" aria-live="polite">
                            <p class="mb-1 text-xs font-semibold uppercase tracking-wide text-muted">Preview</p>
                            <div class="flex min-h-24 items-center justify-center overflow-hidden rounded-lg border border-dashed border-muted/40 bg-paper p-2">
                                <template x-if="isWebAddress">
                                    <img
                                        x-bind:src="url"
                                        x-show="state === 'loaded'"
                                        x-on:load="state = 'loaded'"
                                        x-on:error="state = 'failed'"
                                        alt="Preview of the linked image"
                                        class="max-h-56 rounded"
                                        referrerpolicy="no-referrer"
                                    >
                                </template>
                                <p x-show="state === 'loading'" class="text-sm text-muted">Loading preview…</p>
                                <p x-show="state === 'invalid'" class="text-sm text-red-700">Enter a full address starting with https://</p>
                                <p x-show="state === 'failed'" class="text-sm text-red-700">This address did not load as an image. Check that it points to the image itself (often ending in .jpg, .png or .webp), not to a web page.</p>
                            </div>
                        </div>
                    </div>
                @endif
                <x-admin.input label="Alt text" name="bodyImageAlt" wire:model="bodyImageAlt" hint="Describe the image for people using screen readers." />
                <x-admin.input label="Caption (optional)" name="bodyImageCaption" wire:model="bodyImageCaption" />

                <div>
                    <x-admin.select label="Place in" name="bodyImagePlacement" wire:model="bodyImagePlacement">
                        <option value="cursor">Where the cursor is in the body</option>
                        <option value="start">At the start of the article</option>
                        @foreach ($bodySections as $section)
                            <optgroup label="{{ $section['level'] === 3 ? '— ' : '' }}{{ $section['text'] }}">
                                <option value="below:{{ $section['key'] }}">Just below the heading</option>
                                <option value="section-end:{{ $section['key'] }}">At the end of this section</option>
                            </optgroup>
                        @endforeach
                        <option value="end">At the end of the article</option>
                    </x-admin.select>
                    <p class="mt-1 text-xs text-muted">
                        {{ $bodySections === [] ? 'Add H2 or H3 headings to the body to place images in specific sections.' : 'Sections come from the H2 and H3 headings in the body.' }}
                        <button type="button" wire:click="$refresh" class="font-medium text-brand hover:underline">Refresh sections</button>
                    </p>
                </div>

                <button type="button" wire:click="insertBodyImage" wire:loading.attr="disabled" wire:target="insertBodyImage,bodyImage,bodyImageUrl" class="btn btn-outline !py-2">Insert into article</button>
            </section>
        </div>

        <aside class="space-y-6">
            <section class="space-y-4 card p-4" aria-labelledby="publish-heading">
                <h2 id="publish-heading" class="font-sans text-sm font-semibold uppercase tracking-wide">Publish</h2>

                <x-admin.select label="Status" name="status" wire:model.live="status">
                    @foreach ($statuses as $statusOption)
                        <option value="{{ $statusOption->value }}">{{ $statusOption->label() }}</option>
                    @endforeach
                </x-admin.select>

                @if ($status === 'scheduled')
                    <x-admin.input label="Publish on" name="published_at" type="datetime-local" wire:model="published_at" hint="East Africa Time (EAT, UTC+3)." />
                @endif

                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" wire:model="is_featured" class="size-4 accent-brand">
                    Featured article
                </label>

                <button type="submit" class="w-full rounded-lg bg-brand px-4 py-3 shadow-sm text-sm font-medium text-white hover:bg-brand/90" wire:loading.attr="disabled" wire:target="save,image">
                    {{ $status === 'published' ? 'Save and publish' : ($status === 'scheduled' ? 'Schedule' : 'Save draft') }}
                </button>
            </section>

            <section class="space-y-4 card p-4" aria-labelledby="details-heading">
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

            <section class="space-y-4 card p-4" aria-labelledby="image-heading">
                <h2 id="image-heading" class="font-sans text-sm font-semibold uppercase tracking-wide">Featured image</h2>

                @if ($image)
                    <img src="{{ $image->temporaryUrl() }}" alt="Preview of the selected image" class="w-full">
                    <button type="button" wire:click="clearImage" class="text-sm text-red-700 hover:underline">Remove selected image</button>
                @elseif ($article?->featured_image && ! $removeImage)
                    <img src="{{ $article->featured_image_url }}" alt="{{ $article->featured_image_alt }}" class="w-full">
                    <button type="button" wire:click="$set('removeImage', true)" wire:confirm="Remove this image? It is deleted when you save the article." class="text-sm text-red-700 hover:underline">Remove current image</button>
                @elseif ($article?->featured_image && $removeImage)
                    <p class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-900" role="status">
                        The current image will be removed when you save.
                        <button type="button" wire:click="$set('removeImage', false)" class="ml-1 font-medium underline">Undo</button>
                    </p>
                @endif

                <x-admin.input label="Upload image" name="image" type="file" wire:key="image-{{ $imageInputVersion }}" wire:model="image" accept="image/jpeg,image/png,image/webp" hint="JPG, PNG or WebP, up to 5 MB. Resized to 1600 px wide." />
                <x-admin.input label="Alt text" name="featured_image_alt" wire:model="featured_image_alt" hint="Describe the image for people using screen readers. Required when there is an image." />
            </section>

            <section class="space-y-4 card p-4" aria-labelledby="seo-heading">
                <h2 id="seo-heading" class="font-sans text-sm font-semibold uppercase tracking-wide">SEO</h2>
                <x-admin.input label="Meta title" name="meta_title" wire:model="meta_title" hint="Optional. Up to 70 characters. Defaults to the title." />
                <x-admin.textarea label="Meta description" name="meta_description" wire:model="meta_description" rows="3" hint="Optional. Up to 160 characters. Defaults to the excerpt." />
            </section>
        </aside>
    </form>
</div>
