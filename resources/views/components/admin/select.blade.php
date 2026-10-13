@props(['label', 'name'])

<div>
    <label for="{{ $name }}" class="block text-sm font-medium">{{ $label }}</label>
    <select id="{{ $name }}" name="{{ $name }}" @error($name) aria-invalid="true" aria-describedby="{{ $name }}-error" @enderror {{ $attributes->merge(['class' => 'mt-1 block w-full border border-muted/40 bg-white px-3 py-2 text-sm']) }}>
        {{ $slot }}
    </select>
    @error($name)
        <p id="{{ $name }}-error" class="mt-1 text-sm text-red-700">{{ $message }}</p>
    @enderror
</div>
