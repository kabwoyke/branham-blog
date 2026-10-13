@props(['label', 'name', 'hint' => null])

<div>
    <label for="{{ $name }}" class="block text-sm font-medium">{{ $label }}</label>
    <textarea id="{{ $name }}" name="{{ $name }}" @error($name) aria-invalid="true" aria-describedby="{{ $name }}-error" @enderror {{ $attributes->merge(['rows' => 3, 'class' => 'mt-1 block w-full rounded-lg border border-muted/40 bg-white px-3 py-2 text-sm']) }}></textarea>
    @if ($hint)
        <p class="mt-1 text-xs text-muted">{{ $hint }}</p>
    @endif
    @error($name)
        <p id="{{ $name }}-error" class="mt-1 text-sm text-red-700">{{ $message }}</p>
    @enderror
</div>
