<?php

namespace Tests\Feature;

use App\Livewire\Admin\Login;
use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{string}>
     */
    public static function protectedRoutes(): array
    {
        return [
            'dashboard' => ['admin.dashboard'],
            'articles' => ['admin.articles.index'],
            'new article' => ['admin.articles.create'],
            'categories' => ['admin.categories.index'],
            'subscribers' => ['admin.subscribers.index'],
            'export' => ['admin.subscribers.export'],
            'messages' => ['admin.messages.index'],
            'settings' => ['admin.settings'],
        ];
    }

    #[DataProvider('protectedRoutes')]
    public function test_guests_are_redirected_to_login(string $routeName): void
    {
        $this->get(route($routeName))->assertRedirect(route('admin.login'));
    }

    public function test_guests_cannot_open_the_edit_screen(): void
    {
        $article = Article::factory()->create();

        $this->get(route('admin.articles.edit', $article))->assertRedirect(route('admin.login'));
    }

    #[DataProvider('protectedRoutes')]
    public function test_non_admin_users_are_forbidden(string $routeName): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route($routeName))
            ->assertForbidden();
    }

    #[DataProvider('protectedRoutes')]
    public function test_admin_can_open_every_screen(string $routeName): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(route($routeName))
            ->assertOk();
    }

    public function test_there_is_no_registration_route(): void
    {
        $this->get('/register')->assertNotFound();
        $this->post('/register')->assertNotFound();
    }

    public function test_admin_pages_are_not_indexable(): void
    {
        $this->get(route('admin.login'))->assertSee('noindex', false);
    }

    public function test_admin_can_sign_in(): void
    {
        $admin = User::factory()->admin()->create(['password' => 'secret-password']);

        Livewire::test(Login::class)
            ->set('email', $admin->email)
            ->set('password', 'secret-password')
            ->call('login')
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin);
    }

    public function test_wrong_credentials_are_rejected(): void
    {
        $admin = User::factory()->admin()->create();

        Livewire::test(Login::class)
            ->set('email', $admin->email)
            ->set('password', 'wrong')
            ->call('login')
            ->assertHasErrors('email');

        $this->assertGuest();
    }

    public function test_sign_in_is_rate_limited(): void
    {
        $admin = User::factory()->admin()->create();
        RateLimiter::clear('x');

        $component = Livewire::test(Login::class)->set('email', $admin->email)->set('password', 'wrong');

        foreach (range(1, 5) as $ignored) {
            $component->call('login');
        }

        $component->set('password', 'password')->call('login')->assertHasErrors('email');
        $this->assertGuest();
    }

    public function test_signed_in_admin_is_sent_away_from_login(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.login'))
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_admin_can_sign_out(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->post(route('admin.logout'))
            ->assertRedirect(route('home'));

        $this->assertGuest();
    }
}
