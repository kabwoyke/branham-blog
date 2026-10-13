@if (session('status') || session('error'))
    <div class="mx-auto w-full max-w-5xl px-4 pt-4 sm:px-6" role="status">
        <div @class([
            'border-l-4 px-4 py-3 text-sm',
            'border-brand bg-brand/10 text-ink' => session('status'),
            'border-red-700 bg-red-50 text-red-900' => ! session('status') && session('error'),
        ])>
            {{ session('status') ?? session('error') }}
        </div>
    </div>
@endif
