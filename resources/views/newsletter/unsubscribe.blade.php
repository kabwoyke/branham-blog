<x-layouts.app title="Unsubscribe" :noindex="true">
    <section class="mx-auto max-w-2xl px-4 py-20 sm:px-6">
        <h1 class="text-3xl font-extrabold sm:text-4xl">Unsubscribe</h1>
        <p class="mt-4 text-lg text-muted">Stop sending new articles to <strong class="text-ink">{{ $subscriber->email }}</strong>?</p>
        <form method="POST" action="{{ route('newsletter.unsubscribe.destroy', $subscriber->unsubscribe_token) }}" class="mt-8">
            @csrf
            <button type="submit" class="btn btn-primary">Yes, unsubscribe me</button>
        </form>
    </section>
</x-layouts.app>
