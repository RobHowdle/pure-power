<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class PublicArtistController extends Controller
{
    public function index(): JsonResponse
    {
        $artists = Cache::remember('public.artists.index', now()->addMinutes(5), function () {
            return Artist::query()
                ->where('is_hidden', false)
                ->where('status', 'published')
                ->orderBy('name')
                ->get()
                ->map(fn(Artist $artist) => $this->transformArtist($artist, false))
                ->values()
                ->all();
        });

        return response()->json($artists);
    }


    public function show(string $slug): JsonResponse
    {
        $artist = Artist::query()
            ->with('gallery')
            ->where('is_hidden', false)
            ->where('status', 'published')
            ->where('slug', $slug)
            ->first();

        if (!$artist) {
            return response()->json([
                'message' => 'Artist not found'
            ], 404);
        }

        return response()->json(
            $this->transformArtist($artist, true)
        );
    }


    private function transformArtist(Artist $artist, bool $withDetails): array
    {
        $data = is_array($artist->data) ? $artist->data : [];

        $genres = is_array($data['genres'] ?? null)
            ? $data['genres']
            : [];

        $links = is_array($data['links'] ?? null)
            ? $data['links']
            : [];

        $gallery = $withDetails
            ? $artist->gallery->map(fn ($image) => [
                'id' => $image->id,
                'url' => '/storage/' . $image->image,
            ])->values()
            : collect();

        $featuredGalleryFile = $withDetails
            ? optional($artist->gallery->firstWhere('featured', true) ?? $artist->gallery->first())->image
            : null;

        $featuredGalleryImage = $featuredGalleryFile
            ? '/storage/' . $featuredGalleryFile
            : null;

        $cardImageUrl = $artist->logo_url ?: $artist->image_url;

        // Prefer dedicated hero image for modal, then featured gallery image, then logo.
        $modalImageUrl = $artist->image_url ?: $featuredGalleryImage ?: $artist->logo_url;


        $payload = [
            'id' => $artist->id,
            'name' => $artist->name,
            'slug' => $artist->slug,
            'image_url' => $artist->image_url,
            'hero' => $artist->image_url,
            'card_image_url' => $cardImageUrl,
            'modal_image_url' => $modalImageUrl,
            'tagline' => $artist->excerpt ?? ($data['tagline'] ?? null),
            'genres' => $genres,
            'links' => $links,
            'logo_url' => $artist->logo_url,
        ];


        if ($withDetails) {

            $payload = [
                ...$payload,

                'location' => $data['location'] ?? null,

                'bio' => $artist->content ?? $artist->excerpt,

                'epk_url' => $artist->epk_file,

                'gallery' => $gallery,

            ];
        }


        return $payload;
    }
}
