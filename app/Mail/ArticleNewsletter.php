<?php

namespace App\Mail;

use App\Models\Article;
use App\Models\Subscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

class ArticleNewsletter extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Article $article, public Subscriber $subscriber) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->article->title);
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.article-newsletter',
            with: [
                'articleUrl' => route('articles.show', $this->article->slug),
                'unsubscribeUrl' => route('newsletter.unsubscribe', $this->subscriber->unsubscribe_token),
            ],
        );
    }

    /**
     * One-click unsubscribe headers (RFC 8058), which Gmail and Yahoo expect from newsletter senders.
     */
    public function headers(): Headers
    {
        return new Headers(text: [
            'List-Unsubscribe' => '<'.route('newsletter.unsubscribe.destroy', $this->subscriber->unsubscribe_token).'>',
            'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
        ]);
    }
}
