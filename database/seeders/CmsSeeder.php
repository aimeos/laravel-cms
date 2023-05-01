<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use Aimeos\Cms\Models\Version;
use Aimeos\Cms\Models\Content;
use Aimeos\Cms\Models\File;
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
        \Aimeos\Cms\Tenancy::$callback = function() {
            return 'demo';
        };

        File::where('tenant_id', 'demo')->forceDelete();
        Version::where('tenant_id', 'demo')->forceDelete();
        Content::where('tenant_id', 'demo')->forceDelete();
        Page::where('tenant_id', 'demo')->forceDelete();

        $home = $this->home();

        $this->addBlog( $home )
            ->addDev( $home )
            ->addHidden( $home )
            ->addDisabled( $home );
    }


    protected function file() : File
    {
        return File::forceCreate([
            'mime' => 'image/jpeg',
            'tag' => 'test',
            'name' => 'Test image',
            'path' => 'test/path/test-image.jpg',
            'previews' => ["1000" => "test/path/test-image-1000.jpg", "500" => "test/path/test-image-500.jpg"],
            'editor' => 'seeder',
        ]);
    }


    protected function content() : Content
    {
        $content = Content::forceCreate([
            'data' => ['type' => 'cms::heading', 'text' => 'Welcome to Laravel CMS'],
            'editor' => 'seeder',
        ]);
        $content->versions()->forceCreate([
            'data' => ['type' => 'cms::heading', 'text' => 'Welcome to Laravel CMS'],
            'published' => true,
            'editor' => 'seeder',
        ]);

        return $content;
    }


    protected function home() : Page
    {
        $page = Page::forceCreate([
            'name' => 'Home',
            'title' => 'Home | Laravel CMS',
            'slug' => '',
            'tag' => 'root',
            'domain' => 'mydomain.tld',
            'meta' => ['cms::meta' => ['type' => 'cms::meta', 'text' => 'Laravel CMS is outstanding']],
            'data' => [['type' => 'cms::heading', 'text' => 'Welcome to Laravel CMS']],
            'status' => 1,
            'editor' => 'seeder',
        ]);
        $page->versions()->forceCreate([
            'meta' => ['cms::meta' => ['type' => 'cms::meta', 'text' => 'Laravel CMS is outstanding']],
            'data' => [['type' => 'cms::heading', 'text' => 'Welcome to Laravel CMS']],
            'published' => true,
            'editor' => 'seeder',
        ]);
        $page->contents()->attach( $this->content()->id );

        return $page;
    }


    protected function addBlog( Page $home)
    {
        $page = Page::forceCreate([
            'name' => 'Blog',
            'title' => 'Blog | Laravel CMS',
            'data' => [
                ['type' => 'cms::heading', 'text' => 'Blog example'],
                ['type' => 'cms::blog']
            ],
            'slug' => 'blog',
            'tag' => 'blog',
            'status' => 1,
            'editor' => 'seeder',
        ]);
        $page->appendToNode( $home )->save();

        return $this->addBlogArticle( $page );
    }


    protected function addBlogArticle( Page $blog )
    {
        $data = [
            [
                'type' => 'cms::article',
                'title' => 'Welcome to Laravel CMS',
                'cover' => [
                    'type' => 'cms::image',
                    'name' => 'Welcome to Laravel CMS',
                    'path' => 'https://aimeos.org/tips/wp-content/uploads/2023/01/ai-ecommerce-2.jpg',
                    'previews' => [
                        1000 => 'https://aimeos.org/tips/wp-content/uploads/2023/01/ai-ecommerce-2.jpg'
                    ],
                ],
                'intro' => 'Laravel CMS is lightweight, lighting fast, easy to use, fully customizable and scalable from one-pagers to millions of pages',
            ],
            ['type' => 'cms::heading', 'level' => 2, 'text' => 'Rethink content management!'],
            ['type' => 'cms::text', 'text' => 'Laravel CMS is exceptional in every way. Headless and API-first!'],
            ['type' => 'cms::heading', 'level' => 2, 'text' => 'API first!'],
            ['type' => 'cms::text', 'text' => 'Use GraphQL for editing the pages, contents and files:'],
            ['type' => 'cms::code', 'language' => 'graphql', 'text' => 'mutation {
  cmsLogin(email: "editor@example.org", password: "secret") {
    name
    email
  }
}'          ],
        ];

        $page = Page::forceCreate([
            'name' => 'Welcome to Laravel CMS',
            'title' => 'Welcome to Laravel CMS | Laravel CMS',
            'slug' => 'welcome-to-laravelcms',
            'tag' => 'article',
            'data' => $data,
            'status' => 1,
            'editor' => 'seeder',
        ]);
        $page->appendToNode( $blog )->save();

        $version = $page->versions()->forceCreate([
            'data' => $data,
            'published' => true,
            'editor' => 'seeder',
        ]);
        $version->files()->attach( $this->file() );

        return $this;
    }


    protected function addDev( Page $home )
    {
        $data = [[
            'type' => 'cms::markdown',
            'text' => '# For Developers

This is content created by GitHub-flavored markdown syntax',
        ]];

        $page = Page::forceCreate([
            'name' => 'Dev',
            'title' => 'For Developer | Laravel CMS',
            'slug' => 'dev',
            'data' => $data,
            'status' => 1,
            'editor' => 'seeder',
        ]);
        $page->appendToNode( $home )->save();

        return $this;
    }


    protected function addDisabled( Page $home )
    {
        $page = Page::forceCreate([
            'name' => 'Disabled',
            'title' => 'Disabled page | Laravel CMS',
            'slug' => 'disabled',
            'tag' => 'disabled',
            'status' => 0,
            'editor' => 'seeder',
        ]);
        $page->appendToNode( $home )->save();

        $child = Page::forceCreate([
            'name' => 'Disabled child',
            'title' => 'Disabled child | Laravel CMS',
            'slug' => 'disabled-child',
            'tag' => 'disabled-child',
            'status' => 1,
            'editor' => 'seeder',
        ]);
        $child->appendToNode( $page )->save();

        return $this;
    }


    protected function addHidden( Page $home )
    {
        $page = Page::forceCreate([
            'name' => 'Hidden',
            'title' => 'Hidden page | Laravel CMS',
            'slug' => 'hidden',
            'tag' => 'hidden',
            'status' => 2,
            'editor' => 'seeder',
        ]);
        $page->appendToNode( $home )->save();

        return $this;
    }
}
