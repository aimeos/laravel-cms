<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use Aimeos\Cms\Models\Content;
use Aimeos\Cms\Models\File;
use Aimeos\Cms\Models\Page;
use Aimeos\Cms\Models\Ref;


class CmsSeeder extends Seeder
{
    /**
     * Seed the CMS database.
     *
     * @return void
     */
    public function run()
    {
        Page::truncate();
        File::truncate();
        Content::truncate();

        $home = $this->home();
        $file = $this->file();

        $this->addBlog( $home, [$file] )
            ->addDev( $home )
            ->addHidden( $home )
            ->addDisabled( $home );
    }


    protected function file()
    {
        $file = File::create([
            'mime' => 'image/jpeg',
            'tag' => 'test',
            'name' => 'Test image',
            'path' => 'test/path/test-image.jpg',
            'previews' => '{"1000": "test/path/test-image-1000.jpg", "500": "test/path/test-image-500.jpg"}',
            'editor' => 'seeder',
        ]);
        $file->save();

        return $file;
    }


    protected function home()
    {
        $content = Content::create([
            'data' => ['type' => 'cms::heading', 'text' => 'Welcome to Laravel CMS'],
            'editor' => 'seeder',
        ]);
        $content->versions()->create([
            'data' => ['type' => 'cms::heading', 'text' => 'Welcome to Laravel CMS'],
            'published' => true,
            'editor' => 'seeder',
        ]);

        $page = Page::create([
            'name' => 'Home',
            'title' => 'Home | LaravelCMS',
            'slug' => '',
            'tag' => 'root',
            'domain' => 'mydomain.tld',
            'data' => ['meta' => ['type' => 'cms::meta', 'text' => 'Laravel CMS is outstanding']],
            'status' => 1,
            'editor' => 'seeder',
        ]);
        $page->versions()->create([
            'data' => ['meta' => ['type' => 'cms::meta', 'text' => 'Laravel CMS is outstanding']],
            'published' => true,
            'editor' => 'seeder',
        ]);

        Ref::create([
            'page_id' => $page->id,
            'content_id' => $content->id,
            'position' => 0,
            'status' => 1,
            'editor' => 'seeder',
        ]);

        return $page;
    }


    protected function addBlog( Page $home, array $files )
    {
        $page = Page::create([
            'name' => 'Blog',
            'title' => 'Blog | LaravelCMS',
            'slug' => 'blog',
            'tag' => 'blog',
            'status' => 1,
            'editor' => 'seeder',
        ]);
        $page->appendToNode( $home )->save();

        $content = Content::create([
            'data' => ['type' => 'cms::heading', 'text' => 'Blog example'],
            'editor' => 'seeder',
        ]);
        $content->versions()->create([
            'data' => ['type' => 'cms::heading', 'text' => 'Blog example'],
            'published' => true,
            'editor' => 'seeder',
        ]);

        Ref::create([
            'page_id' => $page->id,
            'content_id' => $content->id,
            'position' => 0,
            'status' => 1,
            'editor' => 'seeder',
        ]);


        $content = Content::create([
            'data' => ['type' => 'cms::blog'],
            'editor' => 'seeder',
        ]);
        $content->versions()->create([
            'data' => ['type' => 'cms::blog'],
            'published' => true,
            'editor' => 'seeder',
        ]);
        $content->files()->syncWithPivotValues(
            collect( $files )->pluck( 'id' ),
            ['tenant_id' => \Aimeos\Cms\Tenancy::value()]
        );

        Ref::create([
            'page_id' => $page->id,
            'content_id' => $content->id,
            'position' => 1,
            'status' => 1,
            'editor' => 'seeder',
        ]);

        return $this->addBlogArticle( $page );
    }


    protected function addBlogArticle( Page $blog )
    {
        $page = Page::create([
            'name' => 'Welcome to LaravelCMS',
            'title' => 'Welcome to LaravelCMS | LaravelCMS',
            'slug' => 'welcome-to-laravelcms',
            'tag' => 'article',
            'status' => 1,
            'editor' => 'seeder',
        ]);
        $page->appendToNode( $blog )->save();

        $data = [
            'type' => 'cms::article',
            'title' => 'Welcome to LaravelCMS',
            'cover' => [
                'type' => 'cms::image',
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
}'              ],
            ]
        ];

        $content = Content::create([
            'data' => $data,
            'editor' => 'seeder',
        ]);
        $content->versions()->create([
            'data' => $data,
            'published' => true,
            'editor' => 'seeder',
        ]);

        Ref::create([
            'page_id' => $page->id,
            'content_id' => $content->id,
            'position' => 0,
            'status' => 1,
            'editor' => 'seeder',
        ]);

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

        $data = [
            'type' => 'cms::markdown',
            'text' => '# For Developers

This is content created by GitHub-flavored markdown syntax',
        ];
        $content = Content::create([
            'data' => $data,
            'editor' => 'seeder',
        ]);
        $content->versions()->create([
            'data' => $data,
            'published' => true,
            'editor' => 'seeder',
        ]);

        Ref::create([
            'page_id' => $page->id,
            'content_id' => $content->id,
            'position' => 0,
            'status' => 1,
            'editor' => 'seeder',
        ]);

        return $this;
    }


    protected function addDisabled( Page $home )
    {
        $page = Page::create([
            'name' => 'Disabled',
            'title' => 'Disabled page | LaravelCMS',
            'slug' => 'disabled',
            'tag' => 'disabled',
            'status' => 0,
            'editor' => 'seeder',
        ]);
        $page->appendToNode( $home )->save();

        $child = Page::create([
            'name' => 'Disabled child',
            'title' => 'Disabled child | LaravelCMS',
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
        $page = Page::create([
            'name' => 'Hidden',
            'title' => 'Hidden page | LaravelCMS',
            'slug' => 'hidden',
            'tag' => 'hidden',
            'status' => 2,
            'editor' => 'seeder',
        ]);
        $page->appendToNode( $home )->save();

        return $this;
    }
}
