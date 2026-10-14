<x-mail::message>
# Confirm your subscription

Please confirm that you would like to receive new articles from {{ config('app.name') }}.

<x-mail::button :url="$confirmUrl">
Confirm subscription
</x-mail::button>

If you did not ask for this, ignore this email and you will not be subscribed. You can also [opt out]({{ $unsubscribeUrl }}).
</x-mail::message>
