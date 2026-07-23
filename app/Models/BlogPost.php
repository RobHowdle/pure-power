<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlogPost extends Model
{
    protected $table = 'blog_posts';

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image_url',
        'status',
        'is_hidden',
        'published_at',
        'data',
    ];

    protected $casts = [
        'is_hidden' => 'boolean',
        'published_at' => 'datetime',
        'data' => 'array',
    ];
}
