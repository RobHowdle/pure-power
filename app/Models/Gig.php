<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gig extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'venue',
        'city',
        'country',
        'starts_at',
        'ends_at',
        'ticket_url',
        'excerpt',
        'content',
        'status',
        'is_hidden',
        'data',
    ];

    protected $casts = [
        'is_hidden' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'data' => 'array',
    ];
}
