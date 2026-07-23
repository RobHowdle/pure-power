<?php

namespace App\Http\Controllers;

use App\Models\Gig;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class GigController extends Controller
{
    public function index()
    {
        return response()->json(
            Gig::query()
                ->orderByDesc('starts_at')
                ->orderByDesc('created_at')
                ->get()
        );
    }


    public function show(Gig $gig)
    {
        return response()->json($gig);
    }


    public function store(Request $request)
    {
        $validated = $this->validateGig($request);

        $payload = Arr::except($validated, [
            'artists_playing',
            'poster_image'
        ]);

        $data = [];

        if (!empty($validated['artists_playing'])) {
            $data['artists_playing'] = $validated['artists_playing'];
        }

        if ($request->hasFile('poster_image')) {

            $path = $request
                ->file('poster_image')
                ->store('gigs', 'public');

            $data['poster_image_path'] = $path;
            $data['poster_image_url'] = Storage::url($path);
        }

        $payload['data'] = $data;

        return response()->json(
            Gig::create($payload)
        );
    }


    public function update(Request $request, Gig $gig)
    {
        $validated = $this->validateGig($request, $gig);

        $payload = Arr::except($validated, [
            'artists_playing',
            'poster_image'
        ]);

        $data = is_array($gig->data)
            ? $gig->data
            : [];

        if (!empty($validated['artists_playing'])) {
            $data['artists_playing'] = $validated['artists_playing'];
        } else {
            unset($data['artists_playing']);
        }


        if ($request->hasFile('poster_image')) {

            if (
                !empty($data['poster_image_path']) &&
                Storage::disk('public')->exists($data['poster_image_path'])
            ) {
                Storage::disk('public')
                    ->delete($data['poster_image_path']);
            }


            $path = $request
                ->file('poster_image')
                ->store('gigs', 'public');


            $data['poster_image_path'] = $path;
            $data['poster_image_url'] = Storage::url($path);
        }


        $payload['data'] = $data;


        $gig->update($payload);


        return response()->json(
            $gig->fresh()
        );
    }


    public function destroy(Gig $gig)
    {
        $gig->delete();

        return response()->json([
            'message' => 'Gig deleted'
        ]);
    }


    public function toggleHidden(Gig $gig)
    {
        $gig->is_hidden = !$gig->is_hidden;
        $gig->save();

        return response()->json($gig);
    }


    private function validateGig(Request $request, Gig $gig = null)
    {
        return $request->validate([
            'title' => 'required|string|max:255',

            'slug' => [
                'required',
                'string',
                'max:255',
                'unique:gigs,slug,' . ($gig?->id ?? 'NULL')
            ],

            'status' => 'required|in:draft,published',

            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date',

            'venue' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',

            'ticket_url' => 'nullable|string|max:2048',

            'artists_playing' => 'nullable|string',

            'content' => 'nullable|string',

            'poster_image' => 'nullable|image|max:5120',
        ]);
    }
}