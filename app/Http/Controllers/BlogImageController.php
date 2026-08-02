<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BlogImageController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'image' => [
                'required',
                'image',
                'max:20480',
            ],
        ]);

        $path = $request
            ->file('image')
            ->store('blog/content', 'public');

        return response()->json([
            'url' => Storage::url($path),
        ]);
    }
}
