@props(['label', 'model', 'name' => $model])

<div>
    <div
        wire:ignore
        x-on:markdown-insert.window="if ($event.detail.editor === '{{ $model }}') { insertMarkdown($event.detail.markdown) }"
        x-on:markdown-set.window="if ($event.detail.editor === '{{ $model }}') { setMarkdown($event.detail.markdown) }"
        x-data="{
            editor: null,
            mode: 'markdown',
            range: null,
            images: [],
            failedImages: {},
            init() {
                this.editor = window.makeMarkdownEditor(this.$refs.editor, $wire.{{ $model }}, (value) => {
                    $wire.{{ $model }} = value
                    this.refreshImages(value)
                })
                this.refreshImages(this.editor.value())
                try {
                    if (localStorage.getItem('editor-mode') === 'rich') { this.setMode('rich') }
                } catch (e) {}
            },
            destroy() {
                this.editor?.toTextArea()
            },
            setMode(mode) {
                if (mode === this.mode) { return }
                if (mode === 'rich') {
                    this.$refs.rich.innerHTML = window.richText.sanitizeHtml(this.editor.markdown(this.editor.value()))
                } else {
                    this.editor.value(window.richText.htmlToMarkdown(this.$refs.rich))
                }
                this.mode = mode
                try { localStorage.setItem('editor-mode', mode) } catch (e) {}
                this.$nextTick(() => { mode === 'markdown' ? this.editor.codemirror.refresh() : this.$refs.rich.focus() })
            },
            syncFromRich() {
                const markdown = window.richText.htmlToMarkdown(this.$refs.rich)
                $wire.{{ $model }} = markdown
                this.refreshImages(markdown)
            },
            refreshImages(markdown) {
                const pattern = /!\[([^\]]*)\]\(\s*<?([^\s)>]+)>?(?:\s+&quot;([^&quot;]*)&quot;)?\s*\)/g
                const found = []
                for (const match of (markdown || '').matchAll(pattern)) {
                    const src = match[2]
                    if (/^(https?:\/\/|\/)/i.test(src)) {
                        found.push({ alt: match[1].trim(), src, caption: (match[3] || '').trim(), external: /^https?:\/\//i.test(src) && ! src.startsWith(window.location.origin) })
                    }
                }
                this.images = found
            },
            saveRange() {
                const selection = window.getSelection()
                if (selection.rangeCount && this.$refs.rich.contains(selection.anchorNode)) {
                    this.range = selection.getRangeAt(0).cloneRange()
                }
            },
            format(command, value = null) {
                this.$refs.rich.focus()
                document.execCommand(command, false, value)
                this.syncFromRich()
            },
            toggleBlock(tag) {
                const current = document.queryCommandValue('formatBlock').toLowerCase()
                this.format('formatBlock', current === tag ? 'p' : tag)
            },
            addLink() {
                const url = window.prompt('Link address (https://…)')
                if (url && window.richText.isSafeUrl(url)) { this.format('createLink', url) }
            },
            insertMarkdown(markdown) {
                if (this.mode === 'markdown') {
                    this.editor.codemirror.replaceSelection(markdown)
                    this.editor.codemirror.focus()
                    return
                }
                const rich = this.$refs.rich
                rich.focus()
                const selection = window.getSelection()
                if (this.range) {
                    selection.removeAllRanges()
                    selection.addRange(this.range)
                } else {
                    selection.selectAllChildren(rich)
                    selection.collapseToEnd()
                }
                document.execCommand('insertHTML', false, window.richText.sanitizeHtml(this.editor.markdown(markdown)))
                this.syncFromRich()
            },
            setMarkdown(markdown) {
                this.editor.value(markdown)
                this.refreshImages(markdown)
                if (this.mode === 'rich') {
                    this.$refs.rich.innerHTML = window.richText.sanitizeHtml(this.editor.markdown(markdown))
                    this.range = null
                }
            },
            pastePlain(event) {
                event.preventDefault()
                document.execCommand('insertText', false, event.clipboardData.getData('text/plain'))
            },
        }"
    >
        <div class="mb-2 flex items-center justify-between gap-4">
            <label for="{{ $name }}-editor" class="block text-sm font-medium">{{ $label }}</label>

            <div role="group" aria-label="Editor mode" class="inline-flex rounded-lg border border-muted/30 bg-white p-0.5 text-xs font-semibold">
                <button type="button" x-on:click="setMode('markdown')" x-bind:aria-pressed="mode === 'markdown'" x-bind:class="mode === 'markdown' ? 'bg-brand text-white' : 'text-muted hover:text-brand'" class="rounded-md px-3 py-1.5 transition-colors">Markdown</button>
                <button type="button" x-on:click="setMode('rich')" x-bind:aria-pressed="mode === 'rich'" x-bind:class="mode === 'rich' ? 'bg-brand text-white' : 'text-muted hover:text-brand'" class="rounded-md px-3 py-1.5 transition-colors">Rich text</button>
            </div>
        </div>

        <div x-show="mode === 'markdown'">
            <textarea id="{{ $name }}-editor" x-ref="editor"></textarea>
        </div>

        <div x-cloak x-show="mode === 'rich'">
            <div role="toolbar" aria-label="Formatting" class="flex flex-wrap items-center gap-1 rounded-t-lg border border-b-0 border-muted/40 bg-white px-2 py-1.5 text-sm">
                <button type="button" x-on:mousedown.prevent x-on:click="format('bold')" title="Bold" class="rounded px-2.5 py-1 font-bold hover:bg-brand/10">B</button>
                <button type="button" x-on:mousedown.prevent x-on:click="format('italic')" title="Italic" class="rounded px-2.5 py-1 italic hover:bg-brand/10">I</button>
                <span class="mx-1 h-5 w-px bg-muted/30" aria-hidden="true"></span>
                <button type="button" x-on:mousedown.prevent x-on:click="toggleBlock('h2')" title="Heading" class="rounded px-2.5 py-1 font-semibold hover:bg-brand/10">H2</button>
                <button type="button" x-on:mousedown.prevent x-on:click="toggleBlock('h3')" title="Subheading" class="rounded px-2.5 py-1 font-semibold hover:bg-brand/10">H3</button>
                <button type="button" x-on:mousedown.prevent x-on:click="toggleBlock('blockquote')" title="Quote" class="rounded px-2.5 py-1 hover:bg-brand/10">Quote</button>
                <span class="mx-1 h-5 w-px bg-muted/30" aria-hidden="true"></span>
                <button type="button" x-on:mousedown.prevent x-on:click="format('insertUnorderedList')" title="Bulleted list" class="rounded px-2.5 py-1 hover:bg-brand/10">List</button>
                <button type="button" x-on:mousedown.prevent x-on:click="format('insertOrderedList')" title="Numbered list" class="rounded px-2.5 py-1 hover:bg-brand/10">1. List</button>
                <span class="mx-1 h-5 w-px bg-muted/30" aria-hidden="true"></span>
                <button type="button" x-on:mousedown.prevent x-on:click="addLink()" title="Insert link" class="rounded px-2.5 py-1 hover:bg-brand/10">Link</button>
                <button type="button" x-on:mousedown.prevent x-on:click="format('unlink')" title="Remove link" class="rounded px-2.5 py-1 hover:bg-brand/10">Unlink</button>
                <button type="button" x-on:mousedown.prevent x-on:click="format('insertHorizontalRule')" title="Divider" class="rounded px-2.5 py-1 hover:bg-brand/10">&mdash;</button>
            </div>
            <div
                x-ref="rich"
                contenteditable="true"
                role="textbox"
                aria-multiline="true"
                aria-label="{{ $label }}"
                x-on:input="syncFromRich()"
                x-on:keyup="saveRange()"
                x-on:mouseup="saveRange()"
                x-on:blur="saveRange()"
                x-on:paste="pastePlain($event)"
                class="prose min-h-[420px] max-w-none rounded-b-lg border border-muted/40 bg-white p-4 focus:border-brand prose-headings:text-ink prose-a:text-brand"
            ></div>
        </div>

        <section x-cloak x-show="images.length > 0" class="mt-3 rounded-lg border border-muted/30 bg-white p-3" aria-label="Images in the {{ strtolower($label) }}">
            <p class="text-xs font-semibold uppercase tracking-wide text-muted">
                Images in this {{ strtolower($label) === 'body' ? 'article' : 'text' }} (<span x-text="images.length"></span>)
            </p>
            <ul class="mt-2 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                <template x-for="(image, index) in images" x-bind:key="index + image.src">
                    <li class="min-w-0">
                        <div class="relative flex aspect-video items-center justify-center overflow-hidden rounded-md bg-paper">
                            <img
                                x-bind:src="image.src"
                                x-bind:alt="image.alt || 'Image without alt text'"
                                x-show="! failedImages[image.src]"
                                x-on:error="failedImages[image.src] = true"
                                x-on:load="delete failedImages[image.src]"
                                loading="lazy"
                                referrerpolicy="no-referrer"
                                class="size-full object-cover"
                            >
                            <p x-show="failedImages[image.src]" class="px-2 text-center text-xs text-red-700">Could not load this image</p>
                            <span x-show="image.external" class="absolute left-1 top-1 rounded bg-ink/70 px-1.5 py-0.5 text-[0.65rem] font-semibold text-white">Linked</span>
                        </div>
                        <p class="mt-1 truncate text-xs" x-bind:title="image.alt" x-bind:class="image.alt ? 'text-ink' : 'text-red-700'" x-text="image.alt || 'Missing alt text'"></p>
                        <p x-show="image.caption" class="truncate text-xs text-muted" x-bind:title="image.caption" x-text="image.caption"></p>
                    </li>
                </template>
            </ul>
        </section>
    </div>
    @error($model)
        <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
    @enderror
</div>
