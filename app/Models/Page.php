<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'content',
        'status',
        'is_hidden',
        'published_at',
        'is_home',
    ];

    /**
     * Set this page as the home page, unsetting all others.
     */
    public function setAsHome()
    {
        // Unset all other home pages
        self::where('is_home', true)->update(['is_home' => false]);
        // Set this page as home
        $this->is_home = true;
        $this->save();
    }
}
