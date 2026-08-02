<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PageController extends Controller
{
    /**
     * Admin: List pages.
     */
    public function index()
    {
        return response()->json(
            Page::query()
                ->orderByDesc('is_home')
                ->orderBy('title')
                ->get()
        );
    }

    /**
     * Admin: Get page data for editing.
     */
    public function edit(Page $page)
    {
        return response()->json($page);
    }

    /**
     * Display the specified page by slug or title.
     */
    public function show($identifier)
    {
        $identifier = strtolower((string) $identifier);

        $payload = $identifier === 'home'
            ? Cache::remember(
                'public.pages.home',
                now()->addMinutes(5),
                fn () => $this->publicPagePayload($identifier)
            )
            : $this->publicPagePayload($identifier);

        if (! $payload) {
            return response()->json(['message' => 'Page not found'], 404);
        }

        return response()->json($payload);
    }

    private function publicPagePayload(string $identifier): ?array
    {

        $pageQuery = Page::query()
            ->where('is_hidden', false)
            ->where('status', 'published');

        if ($identifier === 'home') {
            $pageQuery->where(function ($query) {
                $query
                    ->where('is_home', true)
                    ->orWhere('slug', 'home');
            });
        } else {
            $pageQuery->where(function ($query) use ($identifier) {
                $query
                    ->whereRaw('LOWER(slug) = ?', [$identifier])
                    ->orWhereRaw('LOWER(title) = ?', [$identifier]);
            });
        }

        $page = $pageQuery->first();
        if (! $page) {
            return null;
        }

        // Keep `content` as stored, but also provide parsed `blocks` for consumers.
        $blocks = [];
        if (is_string($page->content) && $page->content !== '') {
            $decoded = json_decode($page->content, true);
            if (is_array($decoded)) {
                $blocks = $decoded;
            }
        }

        return [
            'id' => $page->id,
            'title' => $page->title,
            'slug' => $page->slug,
            'status' => $page->status,
            'published_at' => $page->published_at,
            'content' => $page->content,
            'blocks' => $blocks,
        ];
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:pages,slug',
            'status' => 'required|in:draft,published',
            'content' => 'nullable|string',
        ]);

        if ($validated['status'] === 'published') {
            $validated['published_at'] = now();
        }

        $page = Page::create($validated);

        Cache::forget('public.pages.home');

        return response()->json($page, 201);
    }

    /**
     * Admin: Update content/status.
     */
    public function update(Request $request, Page $page)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|max:255|unique:pages,slug,'.$page->id,
            'status' => 'required|in:draft,published',
            'content' => 'nullable|string',
        ]);

        $page->fill($validated);

        if ($validated['status'] === 'published' && $page->published_at === null) {
            $page->published_at = now();
        }

        if ($validated['status'] === 'draft') {
            $page->published_at = null;
        }

        $page->save();
        Cache::forget('public.pages.home');

        return response()->json([
            'message' => 'Page updated successfully.',
            'page' => $page,
        ]);
    }

    /**
     * Admin: Delete page and its stored content.
     */
    public function destroy(Page $page)
    {
        $page->delete();
        Cache::forget('public.pages.home');

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Admin: Hide/unhide page (separate from draft/published).
     */
    public function toggleHidden(Page $page)
    {
        $page->is_hidden = ! $page->is_hidden;
        $page->save();
        Cache::forget('public.pages.home');

        return response()->json($page);
    }

    /**
     * Set the given page as the home page.
     */
    public function setHome(Page $page)
    {
        $page->setAsHome();
        Cache::forget('public.pages.home');

        return response()->json([
            'message' => 'Home page set successfully.',
        ]);
    }
}
