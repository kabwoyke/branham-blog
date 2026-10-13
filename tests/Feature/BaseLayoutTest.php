<?php

namespace Tests\Feature;

use Tests\TestCase;

class BaseLayoutTest extends TestCase
{
    public function test_layout_renders_wordmark_and_navigation(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Branham Samwel')
            ->assertSee('Skip to content')
            ->assertSee(route('articles.index'), false)
            ->assertSee(route('about'), false)
            ->assertSee(route('contact'), false);
    }

    public function test_placeholder_pages_render(): void
    {
        foreach (['articles.index', 'about', 'contact'] as $name) {
            $this->get(route($name))->assertOk();
        }
    }

    public function test_flash_message_is_displayed(): void
    {
        $this->withSession(['status' => 'Saved successfully.'])
            ->get(route('home'))
            ->assertSee('Saved successfully.');
    }

    public function test_app_name_is_configured(): void
    {
        $this->assertSame('Branham Samwel', config('app.name'));
    }
}
