<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArtistGallery extends Model
{
    protected $table = 'artist_gallery';

    protected $fillable = [
        'artist_id',
        'image',
        'thumbnail',
        'caption',
        'photographer',
        'featured',
        'sort_order',
    ];


    protected $casts = [
        'featured' => 'boolean',
    ];


    protected $appends = [
        'image_url',
        'thumbnail_url',
    ];


    public function artist(): BelongsTo
    {
        return $this->belongsTo(Artist::class);
    }


    public function getImageUrlAttribute(): ?string
    {
        return $this->image
            ? asset('storage/' . $this->image)
            : null;
    }


    public function getThumbnailUrlAttribute(): ?string
    {
        return $this->thumbnail
            ? asset('storage/' . $this->thumbnail)
            : null;
    }
}