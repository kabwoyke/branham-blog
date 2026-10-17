<?php

namespace Tests\Feature;

use Tests\TestCase;

class OfflinePageTest extends TestCase
{
    public function test_offline_page_and_service_worker_are_shipped(): void
    {
        $this->assertFileExists(public_path('offline.html'));
        $this->assertFileExists(public_path('sw.js'));
        $this->assertStringContainsString('/offline.html', file_get_contents(public_path('sw.js')));
    }
}
