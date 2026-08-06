<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use App\Models\BlogPost;
use App\Models\Gig;
use App\Models\Page;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $now = now();
        $artistsMissingImages = Artist::query()
            ->where(function ($query) {
                $query->whereNull('image_url')->orWhere('image_url', '');
            })
            ->count();

        $artistsMissingLogos = Artist::query()
            ->where(function ($query) {
                $query->whereNull('logo_url')->orWhere('logo_url', '');
            })
            ->count();

        $upcomingGigsMissingTickets = Gig::query()
            ->where('status', 'published')
            ->where('is_hidden', false)
            ->whereNotNull('starts_at')
            ->where('starts_at', '>=', $now)
            ->where(function ($query) {
                $query->whereNull('ticket_url')->orWhere('ticket_url', '');
            })
            ->count();

        $homepage = $this->findHomepage();

        $recentActivity = $this->recentActivity();

        return response()->json([
            'user' => [
                'name' => request()->user()?->name,
            ],
            'summary' => [
                'artists' => Artist::count(),
                'upcoming_gigs' => Gig::query()
                    ->where('status', 'published')
                    ->where('is_hidden', false)
                    ->whereNotNull('starts_at')
                    ->where('starts_at', '>=', $now)
                    ->count(),
                'blog_posts' => BlogPost::count(),
                'published_pages' => Page::query()
                    ->where('status', 'published')
                    ->where('is_hidden', false)
                    ->count(),
            ],
            'recent_activity' => $recentActivity,
            'health' => [
                [
                    'status' => $homepage && $homepage->status === 'published' && ! $homepage->is_hidden ? 'ok' : 'warning',
                    'label' => $this->formatHomepageHealthMessage($homepage),
                ],
                [
                    'status' => $artistsMissingImages === 0 ? 'ok' : 'warning',
                    'label' => $this->formatCountMessage(
                        $artistsMissingImages,
                        'artist is missing an image.',
                        'artists are missing images.',
                        'All artists have images.'
                    ),
                ],
                [
                    'status' => $artistsMissingLogos === 0 ? 'ok' : 'warning',
                    'label' => $this->formatCountMessage(
                        $artistsMissingLogos,
                        'artist is missing a logo.',
                        'artists are missing logos.',
                        'All artists have logos.'
                    ),
                ],
                [
                    'status' => $upcomingGigsMissingTickets === 0 ? 'ok' : 'warning',
                    'label' => $this->formatCountMessage(
                        $upcomingGigsMissingTickets,
                        'upcoming gig is missing a ticket link.',
                        'upcoming gigs are missing ticket links.',
                        'All upcoming gigs have ticket links.'
                    ),
                ],
            ],
            'analytics' => [
                'enabled' => false,
                'message' => 'Audience analytics is not enabled yet. Add visit tracking to unlock website views, monthly charts, and most-viewed artists.',
            ],
        ]);
    }

    private function recentActivity(): array
    {
        $activity = collect()
            ->concat($this->mapActivity(Artist::query()->latest('updated_at')->limit(3)->get(), 'artist', '/admin/artists'))
            ->concat($this->mapActivity(Gig::query()->latest('updated_at')->limit(3)->get(), 'gig', '/admin/gigs'))
            ->concat($this->mapActivity(BlogPost::query()->latest('updated_at')->limit(3)->get(), 'blog post', '/admin/blog'))
            ->concat($this->mapActivity(Page::query()->latest('updated_at')->limit(3)->get(), 'page', '/admin/pages'))
            ->sortByDesc('timestamp')
            ->take(6)
            ->values();

        return $activity->all();
    }

    private function mapActivity(EloquentCollection $items, string $resourceLabel, string $basePath): Collection
    {
        return $items->map(function ($item) use ($resourceLabel, $basePath) {
            $action = $item->created_at && $item->updated_at && $item->created_at->equalTo($item->updated_at)
                ? 'created'
                : 'updated';

            return [
                'label' => ucfirst($resourceLabel).' '.$action,
                'subject' => $item->name ?? $item->title ?? $item->slug,
                'timestamp' => $item->updated_at?->toIso8601String(),
                'href' => $basePath.'/'.$item->id.(str_contains($basePath, '/edit') ? '' : '/edit'),
            ];
        });
    }

    private function findHomepage(): ?Page
    {
        return Page::query()
            ->where(function ($query) {
                $query
                    ->where('is_home', true)
                    ->orWhereRaw('LOWER(slug) = ?', ['home'])
                    ->orWhereRaw('LOWER(title) = ?', ['home']);
            })
            ->first();
    }

    private function formatHomepageHealthMessage(?Page $homepage): string
    {
        if (! $homepage) {
            return 'No homepage page has been set yet.';
        }

        if ($homepage->status !== 'published') {
            return 'Homepage is not published yet.';
        }

        if ($homepage->is_hidden) {
            return 'Homepage is hidden and not visible.';
        }

        return 'Homepage is published and visible.';
    }

    private function formatCountMessage(int $count, string $singular, string $plural, string $empty): string
    {
        if ($count === 0) {
            return $empty;
        }

        return $count.' '.($count === 1 ? $singular : $plural);
    }
}
