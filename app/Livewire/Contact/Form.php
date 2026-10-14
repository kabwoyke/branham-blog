<?php

namespace App\Livewire\Contact;

use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use App\Models\Setting;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class Form extends Component
{
    private const MAX_ATTEMPTS = 5;

    private const DECAY_SECONDS = 3600;

    public string $name = '';

    public string $email = '';

    public string $subject = '';

    public string $message = '';

    /** Honeypot: hidden from people, filled in by simple bots. */
    public string $website = '';

    public bool $sent = false;

    /**
     * @return array<string, list<string>>
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'subject' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
        ];
    }

    public function send(): void
    {
        $validated = $this->validate();

        if ($this->website !== '') {
            $this->sent = true;

            return;
        }

        $throttleKey = 'contact:'.request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_ATTEMPTS)) {
            $minutes = (int) ceil(RateLimiter::availableIn($throttleKey) / 60);
            $this->addError('message', "You have sent several messages recently. Please try again in {$minutes} minutes.");

            return;
        }

        RateLimiter::hit($throttleKey, self::DECAY_SECONDS);

        $contactMessage = ContactMessage::create($validated);

        $recipient = Setting::get('contact_email', 'branham@narrisinternational.com');
        rescue(fn () => Mail::to($recipient)->send(new ContactMessageReceived($contactMessage)));

        $this->reset('name', 'email', 'subject', 'message', 'website');
        $this->sent = true;
    }

    public function render(): View
    {
        return view('livewire.contact.form');
    }
}
