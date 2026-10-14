<x-layouts.app title="Contact" description="Get in touch with Branham Samwel.">
    <section class="mx-auto grid max-w-6xl gap-12 px-4 pt-14 sm:px-6 lg:grid-cols-[1fr_20rem]">
        <div>
            <h1 class="text-4xl font-extrabold sm:text-5xl">Contact</h1>
            <p class="mt-3 max-w-2xl text-lg text-muted">Questions, ideas or a conversation worth having. Send a message and you will get a reply by email.</p>
            <div class="mt-8 max-w-2xl">
                <livewire:contact.form />
            </div>
        </div>

        <aside aria-label="Other ways to reach Branham" class="lg:pt-20">
            <ul class="space-y-5 text-sm">
                @if ($email)
                    <li><span class="block font-semibold">Email</span><a href="mailto:{{ $email }}" class="text-brand hover:underline">{{ $email }}</a></li>
                @endif
                @if ($phone)
                    <li>
                        <span class="block font-semibold">Phone / WhatsApp</span>
                        @if ($whatsappUrl)
                            <a href="{{ $whatsappUrl }}" rel="noopener" target="_blank" class="text-brand hover:underline">{{ $phone }} (WhatsApp)</a>
                        @else
                            <span>{{ $phone }}</span>
                        @endif
                    </li>
                @endif
                @if ($linkedin)
                    <li><span class="block font-semibold">LinkedIn</span><a href="{{ $linkedin }}" rel="noopener" target="_blank" class="text-brand hover:underline">Connect on LinkedIn</a></li>
                @endif
            </ul>
        </aside>
    </section>
</x-layouts.app>
