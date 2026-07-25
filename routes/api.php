<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ArtistController;
use App\Http\Controllers\BlogPostController;
use App\Http\Controllers\GigController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\PublicArtistController;
use App\Http\Controllers\Api\PublicBlogPostController;
use App\Http\Controllers\Api\PublicGigController;

Route::get('/pages', [PageController::class, 'index']);
Route::get('/pages/{slug}', [PageController::class, 'show']);
Route::prefix('admin')->group(function () {
    Route::get('/pages/{page}', [PageController::class, 'edit']);
    Route::put('/pages/{page}', [PageController::class, 'update']);
    /**
     * Artist Admin Routes
     */
    Route::get('/artists', [ArtistController::class, 'index']);
    Route::get('/artists/{artist}', [ArtistController::class, 'show']);
    Route::post('/artists', [ArtistController::class, 'store']);
    Route::patch('/artists/{artist}', [ArtistController::class, 'update']);
    Route::delete('/artists/{artist}', [ArtistController::class, 'destroy']);
    Route::patch('/artists/{artist}/toggle-hidden', [ArtistController::class, 'toggleHidden']);

    /**
     * Gig Admin Routes
     */
    Route::get('/gigs', [GigController::class, 'index']);
    Route::post('/gigs', [GigController::class, 'store']);
    Route::get('/gigs/{gig}', [GigController::class, 'show']);
    Route::patch('/gigs/{gig}', [GigController::class, 'update']);
    Route::delete('/gigs/{gig}', [GigController::class, 'destroy']);
    Route::patch('/gigs/{gig}/toggle-hidden', [GigController::class, 'toggleHidden']);

    /**
     * Blog Post Admin Routes
     */
    Route::get('/blog', [BlogPostController::class, 'index']);
    Route::get('/blog/{blogPost}', [BlogPostController::class, 'show']);
    Route::post('/blog', [BlogPostController::class, 'store']);
    Route::patch('/blog/{blogPost}', [BlogPostController::class, 'update']);
    Route::delete('/blog/{blogPost}', [BlogPostController::class, 'destroy']);
    Route::patch('/blog/{blogPost}/toggle-hidden', [BlogPostController::class, 'toggleHidden']);
});
Route::get('/gigs/latest', [PublicGigController::class, 'latest']);
Route::get('/gigs/upcoming', [PublicGigController::class, 'upcoming']);
Route::get('/blog-posts', [PublicBlogPostController::class, 'index']);
Route::get('/blog-posts/{slug}', [PublicBlogPostController::class, 'show']);
Route::get('/artists', [PublicArtistController::class, 'index']);
Route::get('/artists/{slug}', [PublicArtistController::class, 'show']);

Route::post('/contact', [ContactController::class, 'send']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});