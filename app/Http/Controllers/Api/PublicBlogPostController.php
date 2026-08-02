<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class PublicBlogPostController extends Controller
{
    public function index(): JsonResponse
    {
        $posts = BlogPost::query()
            ->where('is_hidden', false)
            ->where('status', 'published')
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->get();

        return response()->json(
            $posts->map(fn (BlogPost $post) => $this->transformPost($post))
        );
    }

    public function show(string $slug): JsonResponse
    {
        $post = BlogPost::query()
            ->where('is_hidden', false)
            ->where('status', 'published')
            ->where('slug', $slug)
            ->first();

        if (! $post) {
            return response()->json([
                'message' => 'Post not found',
            ], 404);
        }

        return response()->json(
            $this->transformPost($post, true)
        );
    }

    private function transformPost(
        BlogPost $post,
        bool $withContent = false
    ): array {

        return [
            'id' => $post->id,

            'title' => $post->title,

            'slug' => $post->slug,

            'excerpt' => $post->excerpt
                ?: Str::limit(
                    strip_tags($post->content ?? ''),
                    160
                ),

            'featured_image_url' => $post->featured_image_url,

            'published_at' => optional($post->published_at)
                ->toISOString(),

            ...($withContent ? [
                'content' => $post->content,
        ] : []),
        ];
    }
}
