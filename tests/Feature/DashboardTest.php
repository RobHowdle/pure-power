<?php

namespace Tests\Feature;

use App\Models\Artist;
use App\Models\BlogPost;
use App\Models\Gig;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_endpoint_returns_phase_one_summary_data(): void
    {
        Sanctum::actingAs(User::factory()->create());

        Page::query()->create([
            'title' => 'Home',
            'slug' => 'home',
            'content' => '[]',
            'is_home' => true,
            'status' => 'published',
            'is_hidden' => false,
            'published_at' => now(),
        ]);

        Page::query()->create([
            'title' => 'About',
            'slug' => 'about',
            'content' => '[]',
            'status' => 'published',
            'is_hidden' => false,
            'published_at' => now(),
        ]);

        Artist::query()->create([
            'name' => 'Sleep Token',
            'slug' => 'sleep-token',
            'status' => 'published',
            'image_url' => 'artists/sleep-token.jpg',
            'logo_url' => 'artists/sleep-token-logo.png',
        ]);

        Artist::query()->create([
            'name' => 'Spiritbox',
            'slug' => 'spiritbox',
            'status' => 'draft',
            'image_url' => null,
            'logo_url' => null,
        ]);

        Gig::query()->create([
            'title' => 'Headline Set',
            'slug' => 'headline-set',
            'status' => 'published',
            'is_hidden' => false,
            'starts_at' => now()->addWeek(),
            'ticket_url' => null,
        ]);

        Gig::query()->create([
            'title' => 'Archived Show',
            'slug' => 'archived-show',
            'status' => 'published',
            'is_hidden' => false,
            'starts_at' => now()->subWeek(),
            'ticket_url' => 'https://tickets.example.com/archived-show',
        ]);

        BlogPost::query()->create([
            'title' => 'Festival Update',
            'slug' => 'festival-update',
            'status' => 'published',
            'is_hidden' => false,
            'published_at' => now(),
        ]);

        $response = $this->getJson('/api/admin/dashboard');

        $response
            ->assertOk()
            ->assertJsonPath('summary.artists', 2)
            ->assertJsonPath('summary.upcoming_gigs', 1)
            ->assertJsonPath('summary.blog_posts', 1)
            ->assertJsonPath('summary.published_pages', 2)
            ->assertJsonPath('health.0.status', 'ok')
            ->assertJsonPath('health.1.status', 'warning')
            ->assertJsonPath('health.2.status', 'warning')
            ->assertJsonPath('health.3.status', 'warning')
            ->assertJsonPath('analytics.enabled', false);

        $this->assertCount(6, $response->json('recent_activity'));
    }

    public function test_dashboard_reports_when_the_homepage_is_not_published(): void
    {
        Sanctum::actingAs(User::factory()->create());

        Page::query()->create([
            'title' => 'Home',
            'slug' => 'home',
            'content' => '[]',
            'is_home' => true,
            'status' => 'draft',
            'is_hidden' => false,
            'published_at' => null,
        ]);

        $response = $this->getJson('/api/admin/dashboard');

        $response
            ->assertOk()
            ->assertJsonPath('health.0.status', 'warning')
            ->assertJsonPath('health.0.label', 'Homepage is not published yet.');
    }

    public function test_dashboard_marks_a_slug_home_page_as_homepage_when_it_is_not_flagged(): void
    {
        Sanctum::actingAs(User::factory()->create());

        Page::query()->create([
            'title' => 'Home',
            'slug' => 'home',
            'content' => '[]',
            'is_home' => false,
            'status' => 'published',
            'is_hidden' => false,
            'published_at' => now(),
        ]);

        $response = $this->getJson('/api/admin/dashboard');

        $response
            ->assertOk()
            ->assertJsonPath('health.0.status', 'ok')
            ->assertJsonPath('health.0.label', 'Homepage is published and visible.');
    }

    public function test_dashboard_endpoint_is_available_without_authentication(): void
    {
        $response = $this->getJson('/api/admin/dashboard');

        $response->assertOk();
    }
}
