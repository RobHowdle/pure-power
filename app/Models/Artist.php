<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
    ];

    protected $casts = [
        'is_hidden' => 'boolean',
        'published_at' => 'datetime',
        'data' => 'array',
    ];
}