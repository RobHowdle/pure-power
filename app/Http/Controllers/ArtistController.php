<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ArtistController extends Controller
{
    public function index()
    {
        return response()->json(
            Artist::query()
                ->orderByDesc('created_at')
                ->get()
        );
    }

    public function show(Artist $artist)
    {
        return response()->json(
            $artist->load('gallery')
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:artists,slug,' . $request->id,
            'status' => 'required|in:draft,published',

            'content' => 'nullable|string',
            'excerpt' => 'nullable|string',
            'data' => 'nullable|json',
            'image' => 'nullable|image|max:20480',
            'logo' => 'nullable|image|max:20480',
            'epk' => 'nullable|mimes:pdf|max:51200',
        ]);

        $validated['data'] = $request->filled('data')
            ? json_decode($request->data, true)
            : null;

        $validated['slug'] = $validated['slug']
            ?: Str::slug($validated['name']);


        if ($request->hasFile('image')) {

            $path = $request->file('image')
                ->store('artists', 'public');

            $validated['image_url'] = Storage::url($path);
        }


        if ($request->hasFile('logo')) {

            $path = $request->file('logo')
                ->store('artists/logos', 'public');

            $validated['logo_url'] = Storage::url($path);
        }

        if ($request->hasFile('epk')) {

            $file = $request->file('epk');

            $path = $file->store('artists/epks', 'public');

            $validated['epk_file'] = Storage::url($path);
            $validated['epk_filename'] = $file->getClientOriginalName();
        }

        $artist = Artist::create($validated);


        return response()->json($artist);
    }

    public function update(Request $request, Artist $artist)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:artists,slug,' . $artist->id,
            'status' => 'required|in:draft,published',
            'content' => 'nullable|string',
            'data' => 'nullable|json',

            'image' => 'nullable|image|max:20480',
            'logo' => 'nullable|image|max:20480',
            'epk' => 'nullable|mimes:pdf|max:51200',
        ]);

        $validated['slug'] = $validated['slug']
            ?: Str::slug($validated['name']);

        $validated['data'] = $request->filled('data')
            ? json_decode($request->data, true)
            : null;

        /*
    |--------------------------------------------------------------------------
    | Artist Image
    |--------------------------------------------------------------------------
    */

        if ($request->hasFile('image')) {

            $this->deletePublicFile($artist->image_url);

            $path = $request->file('image')
                ->store('artists', 'public');

            $validated['image_url'] = Storage::url($path);
        }

        /*
    |--------------------------------------------------------------------------
    | Logo
    |--------------------------------------------------------------------------
    */

        if ($request->hasFile('logo')) {

            $this->deletePublicFile($artist->logo_url);

            $path = $request->file('logo')
                ->store('artists/logos', 'public');

            $validated['logo_url'] = Storage::url($path);
        }

        /*
    |--------------------------------------------------------------------------
    | EPK
    |--------------------------------------------------------------------------
    */

        if ($request->hasFile('epk')) {

            $file = $request->file('epk');

            $path = $file->store('artists/epks', 'public');

            $validated['epk_file'] = Storage::url($path);
            $validated['epk_filename'] = $file->getClientOriginalName();
        }

        $artist->update($validated);

        return response()->json($artist->fresh());
    }

    public function destroy(Artist $artist)
    {
        $artist->delete();

        return response()->json([
            'message' => 'Artist deleted'
        ]);
    }


    public function toggleHidden(Artist $artist)
    {
        $artist->is_hidden = !$artist->is_hidden;
        $artist->save();

        return response()->json($artist);
    }

    private function deletePublicFile(?string $url): void
    {
        if (!$url) {
            return;
        }

        Storage::disk('public')->delete(
            str_replace('/storage/', '', $url)
        );
    }
}