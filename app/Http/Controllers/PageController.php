<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Support\Markdown;
use Illuminate\Contracts\View\View;

class PageController extends Controller
{
    public function about(): View
    {
        return view('about', [
            'content' => Markdown::toHtml(Setting::get('about_content', '')),
            'tagline' => Setting::get('tagline'),
        ]);
    }

    public function contact(): View
    {
        $phone = Setting::get('contact_phone');
        $hasCountryCode = $phone !== null && str_starts_with(trim($phone), '+');

        return view('contact', [
            'email' => Setting::get('contact_email'),
            'phone' => $phone,
            'whatsappUrl' => $hasCountryCode ? 'https://wa.me/'.preg_replace('/\D/', '', $phone) : null,
            'linkedin' => Setting::get('linkedin_url'),
        ]);
    }
}
