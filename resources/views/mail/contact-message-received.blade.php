<x-mail::message>
# New message from {{ $contactMessage->name }}

**Email:** {{ $contactMessage->email }}
**Subject:** {{ $contactMessage->subject }}

{{ $contactMessage->message }}

<x-mail::button :url="route('admin.messages.index')">
Open in admin
</x-mail::button>
</x-mail::message>
