<?php

namespace App\Livewire\Admin;

use App\Models\Setting;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Site settings')]
class Settings extends Component
{
    public string $site_name = '';

    public string $tagline = '';

    public string $contact_email = '';

    public string $contact_phone = '';

    public string $linkedin_url = '';

    public string $about_content = '';

    public function mount(): void
    {
        foreach (array_keys($this->rules()) as $key) {
            $this->{$key} = (string) Setting::get($key, '');
        }
    }

    /**
     * @return array<string, list<string>>
     */
    protected function rules(): array
    {
        return [
            'site_name' => ['required', 'string', 'max:100'],
            'tagline' => ['nullable', 'string', 'max:160'],
            'contact_email' => ['required', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'linkedin_url' => ['nullable', 'url:https', 'max:255'],
            'about_content' => ['required', 'string'],
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();

        foreach ($validated as $key => $value) {
            Setting::set($key, $value);
        }

        session()->flash('status', 'Settings saved.');
    }

    public function render(): View
    {
        return view('livewire.admin.settings');
    }
}
