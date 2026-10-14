<?php

namespace App\Mail;

use App\Models\Subscriber;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Facades\URL;

class ConfirmSubscription extends Mailable
{
    public function __construct(public Subscriber $subscriber) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Please confirm your subscription');
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.confirm-subscription',
            with: [
                'confirmUrl' => URL::signedRoute('newsletter.confirm', ['subscriber' => $this->subscriber->id]),
                'unsubscribeUrl' => route('newsletter.unsubscribe', $this->subscriber->unsubscribe_token),
            ],
        );
    }
}
