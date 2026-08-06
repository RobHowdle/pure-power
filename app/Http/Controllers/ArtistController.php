<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;

class ArtistController extends Controller
{
    public function index()
    {
        // dd(Artist::query()->orderByDesc('created_at')->get());

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
            'slug' => 'nullable|string|max:255|unique:artists,slug,'.$request->id,
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

        /*
        |--------------------------------------------------------------------------
        | Artist Image
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('image')) {

            $validated['image_url'] = $this->saveOptimisedImage(
                $request->file('image')
            );

            // If no dedicated card image is uploaded, derive one from the hero image upload.
            if (! $request->hasFile('logo')) {
                $validated['logo_url'] = $this->saveOptimisedCardImage(
                    $request->file('image')
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Logo
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('logo')) {

            $validated['logo_url'] = $this->saveOptimisedLogo(
                $request->file('logo')
            );
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

        $artist = Artist::create($validated);
        Cache::forget('public.artists.index');

        return response()->json($artist);
    }

    public function update(Request $request, Artist $artist)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|nullable|string|max:255|unique:artists,slug,'.$artist->id,
            'status' => 'sometimes|required|in:draft,published',

            'content' => 'sometimes|nullable|string',
            'data' => 'sometimes|nullable|json',

            'image' => 'sometimes|nullable|image|max:20480',
            'logo' => 'sometimes|nullable|image|max:20480',
            'epk' => 'sometimes|nullable|mimes:pdf|max:51200',
        ]);

        if (array_key_exists('name', $validated)) {

            $validated['slug'] = $validated['slug']
                ?? Str::slug($validated['name']);

        }

        if ($request->filled('data')) {

            $incomingData = json_decode(
                $request->data,
                true
            );

            $validated['data'] = array_merge(
                $artist->data ?? [],
                $incomingData
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Artist Image
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('image')) {

            $this->deletePublicFile($artist->image_url);

            $validated['image_url'] = $this->saveOptimisedImage(
                $request->file('image')
            );

            // Keep card and popup images in sync by default when only one file is uploaded.
            if (! $request->hasFile('logo')) {
                $this->deletePublicFile($artist->logo_url);

                $validated['logo_url'] = $this->saveOptimisedCardImage(
                    $request->file('image')
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Logo
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('logo')) {

            $this->deletePublicFile($artist->logo_url);

            $validated['logo_url'] = $this->saveOptimisedLogo(
                $request->file('logo')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | EPK
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('epk')) {

            $file = $request->file('epk');

            $path = $file->store(
                'artists/epks',
                'public'
            );

            $validated['epk_file'] = Storage::url($path);

            $validated['epk_filename'] = $file->getClientOriginalName();
        }

        $artist->update($validated);

        Cache::forget('public.artists.index');

        return response()->json(
            $artist->fresh()
        );
    }

    public function destroy(Artist $artist)
    {
        $artist->delete();
        Cache::forget('public.artists.index');

        return response()->json([
            'message' => 'Artist deleted',
        ]);
    }

    public function toggleHidden(Artist $artist)
    {
        $artist->is_hidden = ! $artist->is_hidden;
        $artist->save();
        Cache::forget('public.artists.index');

        return response()->json($artist);
    }

    private function deletePublicFile(?string $url): void
    {
        if (! $url) {
            return;
        }

        Storage::disk('public')->delete(
            str_replace('/storage/', '', $url)
        );
    }

    private function saveOptimisedImage(
        UploadedFile $file
    ): string {

        $filename = Str::uuid().'.webp';

        $path = storage_path('app/public/artists/hero');

        if (! file_exists($path)) {
            mkdir($path, 0755, true);
        }

        $manager = new ImageManager(
            new Driver
        );

        $manager->decode($file)
            ->scaleDown(1600)
            ->encode(new WebpEncoder(quality: 88))
            ->save(
                "{$path}/{$filename}"
            );

        return Storage::url(
            "artists/hero/{$filename}"
        );
    }

    private function saveOptimisedCardImage(
        UploadedFile $file
    ): string {

        $filename = Str::uuid().'.webp';

        $directory = storage_path('app/public/artists/logos');

        if (! file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        $manager = new ImageManager(
            new Driver
        );

        $manager->decode($file)
            ->cover(800, 800)
            ->encode(new WebpEncoder(quality: 85))
            ->save(
                "{$directory}/{$filename}"
            );

        return Storage::url("artists/logos/{$filename}");
    }

    private function saveOptimisedLogo(
        UploadedFile $file
    ): string {

        $filename = Str::uuid().'.webp';

        $directory = storage_path('app/public/artists/logos');

        if (! file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        $manager = new ImageManager(
            new Driver
        );

        $manager->decode($file)
            ->scaleDown(800)
            ->encode(new WebpEncoder(quality: 90))
            ->save(
                "{$directory}/{$filename}"
            );

        return Storage::url("artists/logos/{$filename}");
    }
}
