<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\PollsForChanges;
use App\Models\Setting;
use App\Support\SocialLinks;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Site settings')]
class Settings extends Component
{
    use PollsForChanges;

    public string $site_name = '';

    public string $tagline = '';

    public string $contact_email = '';

    public string $contact_phone = '';

    public string $linkedin_url = '';

    public string $x_url = '';

    public string $instagram_url = '';

    public string $tiktok_url = '';

    public string $facebook_url = '';

    public string $about_content = '';

    /** True when another tab or session saved settings since this form was loaded. */
    public bool $hasNewerSettings = false;

    public function mount(): void
    {
        $this->loadSettings();
    }

    /**
     * Replace the form values with the latest saved settings.
     */
    public function loadSettings(): void
    {
        foreach (array_keys($this->rules()) as $key) {
            $this->{$key} = (string) Setting::get($key, '');
        }

        $this->hasNewerSettings = false;
        $this->resetErrorBag();
        $this->dispatch('markdown-set', editor: 'about_content', markdown: $this->about_content);
    }

    /**
     * Never overwrite what the author is typing: flag the newer values and let them choose.
     */
    protected function changesDetected(): void
    {
        $this->hasNewerSettings = true;
    }

    /**
     * @return array<string, list<mixed>>
     */
    protected function rules(): array
    {
        $rules = [
            'site_name' => ['required', 'string', 'max:100'],
            'tagline' => ['nullable', 'string', 'max:160'],
            'contact_email' => ['required', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'about_content' => ['required', 'string'],
        ];

        foreach (array_keys(SocialLinks::PROFILES) as $key) {
            $rules[$key] = SocialLinks::rules($key);
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        $messages = ['about_content.required' => 'The About page needs some text.'];

        foreach (SocialLinks::PROFILES as $key => $profile) {
            $messages["{$key}.url"] = "Enter the full {$profile['label']} address, starting with https://.";
        }

        return $messages;
    }

    public function save(): void
    {
        foreach (array_keys(SocialLinks::PROFILES) as $key) {
            $this->{$key} = trim($this->{$key});
        }

        $validated = $this->validate();

        foreach ($validated as $key => $value) {
            Setting::set($key, trim((string) $value));
        }

        $this->hasNewerSettings = false;

        $this->dispatch('notify', type: 'status', message: 'Settings saved.');
    }

    /**
     * @return list<string>
     */
    protected function liveTopics(): array
    {
        return ['settings'];
    }

    public function render(): View
    {
        return view('livewire.admin.settings', ['socialProfiles' => SocialLinks::PROFILES]);
    }
}
