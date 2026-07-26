<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Artist extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'status',
        'content',
        'excerpt',
        'data',
        'image_url',
        'logo_url',
        'epk_file',
        'epk_filename',
    ];

    protected $casts = [
        'is_hidden' => 'boolean',
        'published_at' => 'datetime',
        'data' => 'array',
    ];

    public function gallery(): HasMany
    {
        return $this->hasMany(ArtistGallery::class)
            ->orderBy('sort_order');
    }
}