<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use App\Models\ArtistGallery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GalleryController extends Controller
{
    public function store(Request $request, Artist $artist)
    {
        $validated = $request->validate([
            'image' => 'required|image|max:20480',
            'caption' => 'nullable|string',
            'photographer' => 'nullable|string',
        ]);

        $path = $request->file('image')
            ->store('artists/gallery', 'public');

        $gallery = $artist->gallery()->create([
            'image' => $path,
            'caption' => $validated['caption'] ?? null,
            'photographer' => $validated['photographer'] ?? null,
            'sort_order' => $artist->gallery()->count(),
        ]);

        return response()->json($gallery);
    }

    public function update(Request $request, ArtistGallery $gallery)
    {
        $validated = $request->validate([
            'caption' => 'nullable|string|max:255',
            'photographer' => 'nullable|string|max:255',
            'featured' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $gallery->update($validated);

        return response()->json($gallery);
    }


    public function destroy(ArtistGallery $gallery)
    {
        Storage::disk('public')->delete($gallery->image);

        $gallery->delete();

        return response()->json([
            'message' => 'Deleted'
        ]);
    }
}