<?php

namespace App\Livewire\Newsletter;

use App\Mail\ConfirmSubscription;
use App\Models\Subscriber;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Double opt-in newsletter signup.
 *
 * The response is identical for new, pending and already-confirmed addresses so the form cannot be used to probe the list.
 */
class Signup extends Component
{
    private const MAX_ATTEMPTS = 5;

    private const DECAY_SECONDS = 3600;

    /** Distinguishes form ids when several signups share a page. */
    #[Locked]
    public string $location = 'default';

    public string $email = '';

    /** Honeypot: hidden from people, filled in by simple bots. */
    public string $website = '';

    public bool $submitted = false;

    /**
     * @return array<string, list<string>>
     */
    protected function rules(): array
    {
        return [
            'email' => ['required', 'email:rfc', 'max:255'],
        ];
    }

    public function subscribe(): void
    {
        $this->validate();

        if ($this->website !== '') {
            $this->submitted = true;

            return;
        }

        $throttleKey = 'newsletter:'.request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_ATTEMPTS)) {
            $minutes = (int) ceil(RateLimiter::availableIn($throttleKey) / 60);
            $this->addError('email', "Too many attempts. Please try again in {$minutes} minutes.");

            return;
        }

        RateLimiter::hit($throttleKey, self::DECAY_SECONDS);

        $subscriber = Subscriber::firstOrCreate(['email' => mb_strtolower(trim($this->email))]);

        if (! $subscriber->isConfirmed()) {
            rescue(fn () => Mail::to($subscriber->email)->send(new ConfirmSubscription($subscriber)));
        }

        $this->reset('email', 'website');
        $this->submitted = true;
    }

    public function render(): View
    {
        return view('livewire.newsletter.signup');
    }
}
