<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;

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


        /*
        |--------------------------------------------------------------------------
        | Artist Image
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('image')) {

            $validated['image_url'] = $this->saveOptimisedImage(
                $request->file('image'),
                'artists'
            );
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

            $validated['image_url'] = $this->saveOptimisedImage(
                $request->file('image'),
                'artists'
            );
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


    private function saveOptimisedImage(
        UploadedFile $file,
        string $directory
    ): string {

        $filename = Str::uuid() . '.webp';

        $path = storage_path(
            "app/public/{$directory}/thumbs"
        );

        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }

        $manager = new ImageManager(
            new Driver()
        );

        $manager->decode($file)
            ->cover(400, 300)
            ->encode(new WebpEncoder(quality: 75))
            ->save(
                "{$path}/{$filename}"
            );

        return Storage::url(
            "{$directory}/thumbs/{$filename}"
        );
    }


    private function saveOptimisedLogo(
        UploadedFile $file
    ): string {

        $filename = Str::uuid() . '.webp';

        $directory = storage_path('app/public/artists/logos');

        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        $manager = new ImageManager(
            new Driver()
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