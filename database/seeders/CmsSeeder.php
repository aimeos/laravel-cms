<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Aimeos\Cms\Models\Content;
use Aimeos\Cms\Models\Page;


class CmsSeeder extends Seeder
{
    /**
     * Seed the CMS database.
     *
     * @return void
     */
    public function run()
    {
        $home = Page::create([
            'name' => 'Home',
            'title' => 'Home | LaravelCMS',
            'slug' => '',
            'tag' => 'root',
            'status' => 1,
        ]);
        $home->save();

        $homeContent = Content::create([
            'page_id' => $home->id,
            'status' => 1,
            'data' => [
                ['type' => 'cms::title', 'text' => 'Welcome to Laravel CMS'],
            ],
        ]);
        $homeContent->save();

        $blog = Page::create([
            'name' => 'Blog',
            'title' => 'Blog | LaravelCMS',
            'slug' => 'blog',
            'status' => 1,
        ]);
        $blog->appendToNode($home)->save();

        $blogContent = Content::create([
            'page_id' => $blog->id,
            'status' => 1,
            'data' => [
                ['type' => 'cms::title', 'text' => 'Blog example'],
                ['type' => 'cms::blog'],
            ],
        ]);
        $blogContent->save();

        $article = Page::create([
            'name' => 'Welcome to LaravelCMS',
            'title' => 'Welcome to LaravelCMS | LaravelCMS',
            'slug' => 'welcome-to-laravelcms',
            'status' => 1,
        ]);
        $article->appendToNode($blog)->save();

        $articleContent = Content::create([
            'page_id' => $article->id,
            'status' => 1,
            'data' => [
                [
                    'type' => 'cms::article',
                    'title' => 'Welcome to LaravelCMS',
                    'image' => [
                        'name' => 'Welcome to LaravelCMS',
                        'path' => 'https://aimeos.org/tips/wp-content/uploads/2023/01/ai-ecommerce-2.jpg',
                        'previews' => [
                            1000 => 'https://aimeos.org/tips/wp-content/uploads/2023/01/ai-ecommerce-2.jpg'
                        ],
                    ],
                    'intro' => 'LaravelCMS is lightweight, lighting fast, easy to use, fully customizable and scalable from one-pagers to billions of pages',
                    'content' => [
                        ['type' => 'cms::heading', 'level' => 2, 'text' => 'Rethink content management!'],
                        ['type' => 'cms::text', 'text' => 'LaravelCMS is exceptional in every way. Headless and API-first!'],
                    ]
                ],
            ],
        ]);
        $articleContent->save();
    }
}
