<?php

namespace App\Livewire\Comments;

use App\Models\Article;
use App\Models\Comment;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Section extends Component
{
    private const MAX_ATTEMPTS = 3;

    private const DECAY_SECONDS = 3600;

    #[Locked]
    public int $articleId;

    public string $name = '';

    public string $email = '';

    public string $body = '';

    /** Honeypot: hidden from people, filled in by simple bots. */
    public string $website = '';

    public bool $submitted = false;

    /**
     * @return array<string, list<string>>
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'body' => ['required', 'string', 'min:5', 'max:2000'],
        ];
    }

    public function submit(): void
    {
        $validated = $this->validate();

        if ($this->website !== '') {
            $this->submitted = true;

            return;
        }

        $throttleKey = 'comment:'.request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_ATTEMPTS)) {
            $minutes = (int) ceil(RateLimiter::availableIn($throttleKey) / 60);
            $this->addError('body', "You have commented several times recently. Please try again in {$minutes} minutes.");

            return;
        }

        RateLimiter::hit($throttleKey, self::DECAY_SECONDS);

        Article::published()->findOrFail($this->articleId)->comments()->create($validated);

        $this->reset('name', 'email', 'body', 'website');
        $this->submitted = true;
    }

    public function render(): View
    {
        return view('livewire.comments.section', [
            'comments' => Comment::approved()->where('article_id', $this->articleId)->oldest()->get(),
        ]);
    }
}
