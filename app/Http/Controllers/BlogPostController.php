<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Mews\Purifier\Facades\Purifier;

class BlogPostController extends Controller
{
    public function index()
    {
        return response()->json(
            BlogPost::query()
                ->latest()
                ->get()
        );
    }

    public function show(BlogPost $blogPost)
    {
        return response()->json($blogPost);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:blog_posts,slug',
            'status' => 'required|in:draft,published',
            'content' => 'nullable|string',
            'featured_image' => 'nullable|image|max:20480',
        ]);

        $validated['content'] = Purifier::clean(
            $validated['content'] ?? '',
            $this->blogContentPurifierConfig(),
            function ($config): void {
                if ($definition = $config->maybeGetRawHTMLDefinition()) {
                    $definition->addAttribute(
                        'img',
                        'data-align',
                        'Enum#left,center,right'
                    );

                    $definition->addAttribute(
                        'img',
                        'data-caption',
                        'Text'
                    );
                }
            }
        );

        $validated['slug'] = $this->generateUniqueSlug(
            $validated['slug'] ?? null,
            $validated['title']
        );

        $validated['excerpt'] = $this->buildExcerpt(
            $validated['content'] ?? null
        );

        if ($request->hasFile('featured_image')) {
            $path = $request->file('featured_image')->store('blog', 'public');

            $validated['featured_image_url'] = Storage::url($path);

            $validated['data'] = [
                'featured_image_path' => $path,
            ];
        }

        if ($validated['status'] === 'published') {
            $validated['published_at'] = now();
        }

        $post = BlogPost::create(
            Arr::except($validated, ['featured_image'])
        );

        return response()->json($post, 201);
    }

    public function update(Request $request, BlogPost $blogPost)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:blog_posts,slug,'.$blogPost->id,
            'status' => 'required|in:draft,published',
            'content' => 'nullable|string',
            'featured_image' => 'nullable|image|max:20480',
        ]);

        $validated['content'] = Purifier::clean(
            $validated['content'] ?? '',
            $this->blogContentPurifierConfig(),
            function ($config): void {
                if ($definition = $config->maybeGetRawHTMLDefinition()) {
                    $definition->addAttribute(
                        'img',
                        'data-align',
                        'Enum#left,center,right'
                    );

                    $definition->addAttribute(
                        'img',
                        'data-caption',
                        'Text'
                    );
                }
            }
        );

        $validated['slug'] = $this->generateUniqueSlug(
            $validated['slug'] ?? null,
            $validated['title'],
            $blogPost->id
        );

        $validated['excerpt'] = $this->buildExcerpt(
            $validated['content'] ?? null
        );

        $data = is_array($blogPost->data)
            ? $blogPost->data
            : [];

        if ($request->hasFile('featured_image')) {

            $oldPath = $data['featured_image_path'] ?? null;

            if (
                $oldPath &&
                Storage::disk('public')->exists($oldPath)
            ) {
                Storage::disk('public')->delete($oldPath);
            }

            $path = $request->file('featured_image')->store('blog', 'public');

            $validated['featured_image_url'] = Storage::url($path);

            $data['featured_image_path'] = $path;
        }

        $validated['data'] = $data;

        $blogPost->fill(
            Arr::except($validated, ['featured_image'])
        );

        if (
            $validated['status'] === 'published' &&
            $blogPost->published_at === null
        ) {
            $blogPost->published_at = now();
        }

        if ($validated['status'] === 'draft') {
            $blogPost->published_at = null;
        }

        $blogPost->save();

        return response()->json($blogPost);
    }

    public function destroy(BlogPost $blogPost)
    {
        $data = is_array($blogPost->data)
            ? $blogPost->data
            : [];

        if (
            isset($data['featured_image_path']) &&
            Storage::disk('public')->exists($data['featured_image_path'])
        ) {
            Storage::disk('public')->delete($data['featured_image_path']);
        }

        $blogPost->delete();

        return response()->json([
            'success' => true,
        ]);
    }

    public function toggleHidden(BlogPost $blogPost)
    {
        $blogPost->update([
            'is_hidden' => ! $blogPost->is_hidden,
        ]);

        return response()->json($blogPost);
    }

    private function generateUniqueSlug(
        ?string $slugInput,
        string $title,
        ?int $ignoreId = null
    ): string {
        $base = Str::slug($slugInput ?: $title);

        if ($base === '') {
            $base = 'blog-post';
        }

        $slug = $base;
        $counter = 2;

        while (
            BlogPost::query()
                ->when(
                    $ignoreId,
                    fn ($query) => $query->whereKeyNot($ignoreId)
                )
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    private function buildExcerpt(?string $content): ?string
    {
        $plain = trim(strip_tags((string) $content));

        if ($plain === '') {
            return null;
        }

        return Str::limit($plain, 100, '...');
    }

    private function blogContentPurifierConfig(): array
    {
        return [
            'HTML.Doctype' => 'HTML 4.01 Transitional',
            'HTML.Allowed' => implode(',', [
                'h1',
                'h2',
                'h3',
                'p',
                'strong',
                'em',
                'u',
                's',
                'ul',
                'ol',
                'li',
                'blockquote',
                'pre',
                'a[href|target]',
                'br',
                'img[src|alt|title|width|height|style|data-align|data-caption]',
            ]),
            'CSS.AllowedProperties' => implode(',', [
                'font',
                'font-size',
                'font-weight',
                'font-style',
                'font-family',
                'text-decoration',
                'padding-left',
                'color',
                'background-color',
                'text-align',
                'width',
                'height',
            ]),
            'AutoFormat.AutoParagraph' => true,
            'AutoFormat.RemoveEmpty' => true,
        ];
    }
}
