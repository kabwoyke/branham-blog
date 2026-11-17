<?php

namespace App\Http\Controllers;

use App\Models\Subscriber;
use App\Support\LiveUpdates;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class NewsletterController extends Controller
{
    /**
     * Reached from the emailed signed link, so the route's `signed` middleware guards it.
     */
    public function confirm(Subscriber $subscriber): View
    {
        if (! $subscriber->isConfirmed()) {
            $subscriber->forceFill(['confirmed_at' => now()])->save();
        }

        return view('newsletter.status', [
            'title' => 'Subscription confirmed',
            'message' => 'Thank you. Your subscription is confirmed.',
        ]);
    }

    /**
     * Shows a confirmation step so mail scanners that prefetch links cannot unsubscribe people.
     */
    public function showUnsubscribe(string $token): View
    {
        $subscriber = Subscriber::where('unsubscribe_token', $token)->firstOrFail();

        return view('newsletter.unsubscribe', ['subscriber' => $subscriber]);
    }

    public function unsubscribe(string $token): RedirectResponse
    {
        if (Subscriber::where('unsubscribe_token', $token)->delete() > 0) {
            LiveUpdates::touch('subscribers');
        }

        return redirect()->route('home')->with('status', 'You have been unsubscribed.');
    }
}
