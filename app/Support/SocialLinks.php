<?php

namespace App\Support;

use App\Models\Setting;
use Closure;

/**
 * The author's social media profiles, stored as settings and shown in the footer and on the Contact page.
 */
class SocialLinks
{
    /**
     * Setting key => label, the hosts a profile address may use, and an example address.
     *
     * @var array<string, array{label: string, hosts: list<string>, example: string}>
     */
    public const PROFILES = [
        'linkedin_url' => ['label' => 'LinkedIn', 'hosts' => ['linkedin.com'], 'example' => 'https://www.linkedin.com/in/…'],
        'x_url' => ['label' => 'X', 'hosts' => ['x.com', 'twitter.com'], 'example' => 'https://x.com/…'],
        'instagram_url' => ['label' => 'Instagram', 'hosts' => ['instagram.com'], 'example' => 'https://www.instagram.com/…'],
        'tiktok_url' => ['label' => 'TikTok', 'hosts' => ['tiktok.com'], 'example' => 'https://www.tiktok.com/@…'],
        'facebook_url' => ['label' => 'Facebook', 'hosts' => ['facebook.com', 'fb.com'], 'example' => 'https://www.facebook.com/…'],
    ];

    /**
     * The profiles that have an address, in display order.
     *
     * @return list<array{key: string, label: string, url: string}>
     */
    public static function filled(): array
    {
        $links = [];

        foreach (self::PROFILES as $key => $profile) {
            $url = trim((string) Setting::get($key, ''));

            if ($url !== '') {
                $links[] = ['key' => $key, 'label' => $profile['label'], 'url' => $url];
            }
        }

        return $links;
    }

    /**
     * The X handle (e.g. "@branham") for Twitter card tags, when an X profile is set.
     */
    public static function xHandle(): ?string
    {
        $path = trim((string) parse_url((string) Setting::get('x_url', ''), PHP_URL_PATH), '/');
        $handle = explode('/', $path)[0];

        return preg_match('/^[A-Za-z0-9_]{1,15}$/', $handle) === 1 ? '@'.$handle : null;
    }

    /**
     * Validation rules for one profile address: optional, https, and on the platform's own domain.
     *
     * @return list<mixed>
     */
    public static function rules(string $key): array
    {
        $profile = self::PROFILES[$key];

        return [
            'nullable',
            'url:https',
            'max:255',
            function (string $attribute, mixed $value, Closure $fail) use ($profile): void {
                $host = strtolower((string) parse_url((string) $value, PHP_URL_HOST));

                foreach ($profile['hosts'] as $allowed) {
                    if ($host === $allowed || str_ends_with($host, '.'.$allowed)) {
                        return;
                    }
                }

                $fail("Enter a {$profile['label']} address, like {$profile['example']}");
            },
        ];
    }
}
