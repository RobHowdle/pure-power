<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Page;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        $corePages = [
            [
                'title' => 'Home',
                'slug' => 'home',
                'is_home' => true,
                'status' => 'published',
                'is_hidden' => false,
                'content' => [
                    ['id' => 'home-title', 'type' => 'home_title', 'props' => ['text' => 'PURE POWER']],
                    [
                        'id' => 'home-intro',
                        'type' => 'home_intro',
                        'props' => [
                            'text' => "We are an independent artist management, PR and events company.\n\nWe work with artists and promoters to build memorable shows and sustainable careers.",
                        ],
                    ],
                    [
                        'id' => 'home-latest-gig',
                        'type' => 'latest_gig',
                        'props' => [
                            'fallbackImageUrl' => 'https://upload.wikimedia.org/wikipedia/commons/c/ca/Avenged_Sevenfold_2.jpg',
                            'fallbackTitle' => 'BAND NAME',
                            'fallbackDate' => '1st July, 2025',
                            'fallbackLocation' => 'WEMBLEY STADIUM, LONDON',
                            'fallbackExcerpt' => "Don't miss an unforgettable night of heavy hits",
                            'ticketLabel' => 'TICKETS',
                        ],
                    ],
                    ['id' => 'home-artists', 'type' => 'artists_slider', 'props' => ['title' => 'OUR ARTISTS']],
                    [
                        'id' => 'home-cta',
                        'type' => 'home_cta',
                        'props' => [
                            'heading' => 'READY TO GET YOUR BAND ON STAGE?',
                            'buttonLabel' => 'CONTACT US',
                            'buttonHref' => '/contact',
                        ],
                    ],
                ],
            ],
            [
                'title' => 'About',
                'slug' => 'about',
                'is_home' => false,
                'status' => 'published',
                'is_hidden' => false,
                'content' => [
                    [
                        'id' => 'seed-about-hero',
                        'type' => 'heading',
                        'props' => [
                            'level' => 1,
                            'text' => 'About',
                        ],
                    ],
                    [
                        'id' => 'seed-about-copy',
                        'type' => 'text',
                        'props' => [
                            'text' => 'Tell your story here. You can add headings, text, images, and buttons using the page builder.',
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Artists',
                'slug' => 'artists',
                'is_home' => false,
                'status' => 'published',
                'is_hidden' => false,
                'content' => [
                    [
                        'id' => 'seed-artists-hero',
                        'type' => 'heading',
                        'props' => [
                            'level' => 1,
                            'text' => 'Artists',
                        ],
                    ],
                    [
                        'id' => 'seed-artists-copy',
                        'type' => 'text',
                        'props' => [
                            'text' => 'Use this space for intro copy above your artists listing.',
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Gigs',
                'slug' => 'gigs',
                'is_home' => false,
                'status' => 'published',
                'is_hidden' => false,
                'content' => [
                    [
                        'id' => 'seed-gigs-hero',
                        'type' => 'heading',
                        'props' => [
                            'level' => 1,
                            'text' => 'Gigs',
                        ],
                    ],
                    [
                        'id' => 'seed-gigs-copy',
                        'type' => 'text',
                        'props' => [
                            'text' => 'Use this space for intro copy above upcoming gigs.',
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Blog',
                'slug' => 'blog',
                'is_home' => false,
                'status' => 'published',
                'is_hidden' => false,
                'content' => [
                    [
                        'id' => 'seed-blog-hero',
                        'type' => 'heading',
                        'props' => [
                            'level' => 1,
                            'text' => 'Blog',
                        ],
                    ],
                    [
                        'id' => 'seed-blog-copy',
                        'type' => 'text',
                        'props' => [
                            'text' => 'Use this space for intro copy above your posts.',
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Contact',
                'slug' => 'contact',
                'is_home' => false,
                'status' => 'published',
                'is_hidden' => false,
                'content' => [
                    [
                        'id' => 'seed-contact-hero',
                        'type' => 'heading',
                        'props' => [
                            'level' => 1,
                            'text' => 'Contact',
                        ],
                    ],
                    [
                        'id' => 'seed-contact-copy',
                        'type' => 'text',
                        'props' => [
                            'text' => 'Use this space for your contact intro and call-to-action.',
                        ],
                    ],
                    [
                        'id' => 'seed-contact-btn',
                        'type' => 'button',
                        'props' => [
                            'label' => 'Email us',
                            'href' => 'mailto:hello@purepower.com',
                        ],
                    ],
                ],
            ],
        ];

        foreach ($corePages as $data) {
            $slug = $data['slug'];

            $page = Page::query()->firstOrNew(['slug' => $slug]);

            $page->title = $data['title'];
            $page->slug = $slug;
            $page->status = $data['status'];
            $page->is_hidden = $data['is_hidden'];
            $page->is_home = $data['is_home'];

            if (empty($page->content)) {
                $page->content = json_encode($data['content']);
            }

            if ($page->status === 'published' && $page->published_at === null) {
                $page->published_at = now();
            }

            $page->save();
        }

        // Safety: ensure only one page is marked as home.
        $home = Page::query()->where('slug', 'home')->first();
        if ($home) {
            $home->setAsHome();
        }
    }
}
