<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Defaults are only written when a key does not exist yet, so re-seeding never overwrites admin edits.
     *
     * @var array<string, string>
     */
    private const DEFAULTS = [
        'site_name' => 'Branham Samwel',
        'tagline' => 'Business analyst and social researcher',
        'contact_email' => 'branham@narrisinternational.com',
        'contact_phone' => '0111429478',
        'linkedin_url' => '',
        'about_content' => "Branham Samwel is a business analyst and social researcher.\n\nThis space is for his own thinking: observations from everyday life, what they could mean for society and for business, and where emerging patterns might lead.\n\nThe future is largely unpredictable, but there is real value in anticipating it.",
    ];

    public function run(): void
    {
        foreach (self::DEFAULTS as $key => $value) {
            Setting::firstOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
