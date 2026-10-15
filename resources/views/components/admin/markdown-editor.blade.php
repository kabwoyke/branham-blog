@props(['label', 'model', 'name' => $model])

<div>
    <div
        wire:ignore
        x-on:markdown-insert.window="if ($event.detail.editor === '{{ $model }}') { insertMarkdown($event.detail.markdown) }"
        x-data="{
            editor: null,
            mode: 'markdown',
            range: null,
            init() {
                this.editor = window.makeMarkdownEditor(this.$refs.editor, $wire.{{ $model }}, (value) => { $wire.{{ $model }} = value })
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
                $wire.{{ $model }} = window.richText.htmlToMarkdown(this.$refs.rich)
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
    </div>
    @error($model)
        <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
    @enderror
</div>
