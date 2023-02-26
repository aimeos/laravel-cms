<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
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
        DB::delete( 'DELETE FROM cms_pages' );

        $home = $this->home();

        $this->addBlog( $home )
            ->addDev( $home );
    }


    protected function home()
    {
        $home = Page::create([
            'name' => 'Home',
            'title' => 'Home | LaravelCMS',
            'slug' => '',
            'tag' => 'root',
            'status' => 1,
            'editor' => 'seeder',
        ]);
        $home->save();

        $homeContent = Content::create([
            'page_id' => $home->id,
            'status' => 1,
            'data' => [
                ['type' => 'cms::title', 'text' => 'Welcome to Laravel CMS'],
            ],
            'editor' => 'seeder',
        ]);
        $homeContent->save();

        return $home;
    }


    protected function addBlog( Page $home )
    {
        $page = Page::create([
            'name' => 'Blog',
            'title' => 'Blog | LaravelCMS',
            'slug' => 'blog',
            'status' => 1,
            'editor' => 'seeder',
        ]);
        $page->appendToNode( $home )->save();

        $pageContent = Content::create([
            'page_id' => $page->id,
            'status' => 1,
            'data' => [
                ['type' => 'cms::title', 'text' => 'Blog example'],
                ['type' => 'cms::blog'],
            ],
            'editor' => 'seeder',
        ]);
        $pageContent->save();

        $article = Page::create([
            'name' => 'Welcome to LaravelCMS',
            'title' => 'Welcome to LaravelCMS | LaravelCMS',
            'slug' => 'welcome-to-laravelcms',
            'status' => 1,
            'editor' => 'seeder',
        ]);
        $article->appendToNode($page)->save();

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
                    'intro' => 'LaravelCMS is lightweight, lighting fast, easy to use, fully customizable and scalable from one-pagers to millions of pages',
                    'content' => [
                        ['type' => 'cms::heading', 'level' => 2, 'text' => 'Rethink content management!'],
                        ['type' => 'cms::text', 'text' => 'LaravelCMS is exceptional in every way. Headless and API-first!'],
                        ['type' => 'cms::heading', 'level' => 2, 'text' => 'API first!'],
                        ['type' => 'cms::text', 'text' => 'Use GraphQL for editing the pages, contents and files:'],
                        ['type' => 'cms::code', 'language' => 'graphql', 'text' => 'mutation {
  cmsLogin(email: "editor@example.org", password: "secret") {
    name
    email
  }
}'                      ],
                    ]
                ],
            ],
            'editor' => 'seeder',
        ]);
        $articleContent->save();

        return $this;
    }


    protected function addDev( Page $home )
    {
        $page = Page::create([
            'name' => 'Dev',
            'title' => 'For Developer | LaravelCMS',
            'slug' => 'dev',
            'status' => 1,
            'editor' => 'seeder',
        ]);
        $page->appendToNode( $home )->save();

        $content = Content::create([
            'page_id' => $page->id,
            'status' => 1,
            'data' => [
                ['type' => 'cms::markdown', 'text' => '# For Developers

This is content created by GitHub-flavored markdown syntax'
                ],
            ],
            'editor' => 'seeder',
        ]);
        $content->save();
    }
}
