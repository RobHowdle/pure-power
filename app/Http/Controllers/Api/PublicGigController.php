<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Gig;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PublicGigController extends Controller
{
    public function latest(): JsonResponse
    {
        $gig = Cache::remember('public.gigs.latest', now()->addMinutes(5), function () {
            $base = $this->baseQuery();

            return (clone $base)
                ->whereNotNull('starts_at')
                ->where('starts_at', '>=', now())
                ->orderBy('starts_at')
                ->first()
                ?? (clone $base)
                    ->orderByDesc('starts_at')
                    ->orderByDesc('created_at')
                    ->first();
        });

        return response()->json([
            'gig' => $gig ? $this->transformGig($gig) : null,
        ]);
    }

    public function upcoming(Request $request): JsonResponse
    {
        $months = (int) $request->query('months', 6);
        if ($months < 1) {
            $months = 6;
        }
        if ($months > 24) {
            $months = 24;
        }

        $gigs = $this->baseQuery()
            ->whereNotNull('starts_at')
            ->where('starts_at', '>=', now())
            ->where('starts_at', '<=', now()->copy()->addMonths($months))
            ->orderBy('starts_at')
            ->orderBy('created_at')
            ->get();

        return response()->json($gigs->map(fn (Gig $gig) => $this->transformGig($gig)));
    }

    private function baseQuery()
    {
        return Gig::query()
            ->where('is_hidden', false)
            ->where('status', 'published');
    }

    private function transformGig(Gig $gig): array
    {
        $data = is_array($gig->data) ? $gig->data : [];

        return [
            'id' => $gig->id,
            'title' => $gig->title,
            'slug' => $gig->slug,
            'starts_at' => $gig->starts_at,
            'ends_at' => $gig->ends_at,
            'venue' => $gig->venue,
            'city' => $gig->city,
            'country' => $gig->country,
            'ticket_url' => $gig->ticket_url,
            'poster_image_url' => $data['poster_image_url'] ?? null,
            'artists_playing' => $data['artists_playing'] ?? $gig->excerpt,
            'excerpt' => $gig->excerpt,
        ];
    }
}
