@props(['label', 'model', 'name' => $model])

<div>
    <label for="{{ $name }}-editor" class="mb-1 block text-sm font-medium">{{ $label }}</label>
    <div
        wire:ignore
        x-on:markdown-insert.window="if ($event.detail.editor === '{{ $model }}') { editor.codemirror.replaceSelection($event.detail.markdown); editor.codemirror.focus() }"
        x-data="{
            editor: null,
            init() {
                this.editor = window.makeMarkdownEditor(this.$refs.editor, $wire.{{ $model }}, (value) => { $wire.{{ $model }} = value })
            },
            destroy() {
                this.editor?.toTextArea()
            },
        }"
    >
        <textarea id="{{ $name }}-editor" x-ref="editor"></textarea>
    </div>
    @error($model)
        <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
    @enderror
</div>
