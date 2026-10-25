<?php

namespace App\Mail;

use App\Models\Article;
use App\Models\Subscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
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
}
