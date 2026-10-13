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
}
