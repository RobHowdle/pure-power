<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use Illuminate\Http\JsonResponse;

class PublicArtistController extends Controller
{
    public function index(): JsonResponse
    {
        $artists = Artist::query()
            ->where('is_hidden', false)
            ->where('status', 'published')
            ->orderBy('name')
            ->get();

        return response()->json(
            $artists->map(fn(Artist $artist) => $this->transformArtist($artist, false))
        );
    }

    public function show(string $slug): JsonResponse
    {
        $artist = Artist::query()
            ->where('is_hidden', false)
            ->where('status', 'published')
            ->where('slug', $slug)
            ->first();

        if (!$artist) {
            return response()->json(['message' => 'Artist not found'], 404);
        }

        return response()->json($this->transformArtist($artist, true));
    }

    private function transformArtist(Artist $artist, bool $withDetails): array
    {
        $data = is_array($artist->data) ? $artist->data : [];
        $genres = is_array($data['genres'] ?? null) ? $data['genres'] : [];
        $links = is_array($data['links'] ?? null) ? $data['links'] : [];

        $payload = [
            'id' => $artist->id,
            'name' => $artist->name,
            'slug' => $artist->slug,
            'image_url' => $artist->image_url,
            'hero' => $artist->image_url,
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
                'images' => is_array($data['images'] ?? null) ? $data['images'] : [],
            ];
        }

        return $payload;
    }
}