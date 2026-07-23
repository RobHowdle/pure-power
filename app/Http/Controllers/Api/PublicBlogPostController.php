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
            $posts->map(fn(BlogPost $post) => $this->transformPost($post))
        );
    }


    public function show(string $slug): JsonResponse
    {
        $post = BlogPost::query()
            ->where('is_hidden', false)
            ->where('status', 'published')
            ->where('slug', $slug)
            ->first();

        if (!$post) {
            return response()->json([
                'message' => 'Post not found'
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

        $blocks = $this->extractContentBlocks($post->content);

        return [
            'id' => $post->id,

            'title' => $post->title,

            'slug' => $post->slug,

            'excerpt' => $post->excerpt
                ?: Str::limit(
                    strip_tags(
                        is_string($post->content)
                            ? $post->content
                            : ''
                    ),
                    160
                ),

            'featured_image_url' => $post->featured_image_url,

            'published_at' => optional($post->published_at)
                ->toISOString(),

            'content_blocks' => $blocks,

            ...($withContent ? [
                'content' => $post->content,
            ] : []),
        ];
    }


    private function extractContentBlocks($content): array
    {
        if (empty($content)) {
            return [];
        }


        /*
         * Handle content stored as JSON string
         */
        if (is_string($content)) {

            $decoded = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return [];
            }

            $content = $decoded;
        }


        if (!is_array($content)) {
            return [];
        }


        $blocks = [];


        foreach ($content as $block) {

            if (!is_array($block)) {
                continue;
            }


            $type = $block['type'] ?? null;

            $props = $block['props'] ?? [];


            if (!is_array($props)) {
                $props = [];
            }


            /*
             * Paragraph blocks
             */
            if ($type === 'paragraph') {

                $blocks[] = [
                    'type' => 'paragraph',
                    'text' => $props['text'] ?? '',
                ];

                continue;
            }


            /*
             * Image blocks
             */
            if ($type === 'image') {

                $src = $props['src'] ?? null;

                if (!$src) {
                    continue;
                }


                $blocks[] = [
                    'type' => 'image',
                    'src' => $src,
                    'alt' => $props['alt'] ?? '',
                    'caption' => $props['caption'] ?? null,
                    'align' => $props['align'] ?? 'center',
                ];

                continue;
            }


            /*
             * Preserve future block types
             * so frontend can support them later
             */
            if ($type) {

                $blocks[] = [
                    'type' => $type,
                    'props' => $props,
                ];
            }
        }


        return $blocks;
    }
}