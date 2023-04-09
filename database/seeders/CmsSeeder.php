<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use Aimeos\Cms\Models\Version;
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
        File::where('tenant_id', 'demo')->delete();
        Version::where('tenant_id', 'demo')->delete();
        Content::where('tenant_id', 'demo')->delete();
        Page::where('tenant_id', 'demo')->delete();

        $home = $this->home();

        $this->addBlog( $home )
            ->addDev( $home )
            ->addHidden( $home )
            ->addDisabled( $home );
    }


    protected function file()
    {
        return File::create([
            'tenant_id' => 'demo',
            'mime' => 'image/jpeg',
            'tag' => 'test',
            'name' => 'Test image',
            'path' => 'test/path/test-image.jpg',
            'previews' => ["1000" => "test/path/test-image-1000.jpg", "500" => "test/path/test-image-500.jpg"],
            'editor' => 'seeder',
        ]);
    }


    protected function home()
    {
        $content = Content::create([
            'tenant_id' => 'demo',
            'data' => ['type' => 'cms::heading', 'text' => 'Welcome to Laravel CMS'],
            'editor' => 'seeder',
        ]);
        $content->versions()->create([
            'tenant_id' => 'demo',
            'data' => ['type' => 'cms::heading', 'text' => 'Welcome to Laravel CMS'],
            'published' => true,
            'editor' => 'seeder',
        ]);

        $page = Page::create([
            'tenant_id' => 'demo',
            'name' => 'Home',
            'title' => 'Home | Laravel CMS',
            'slug' => '',
            'tag' => 'root',
            'domain' => 'mydomain.tld',
            'data' => ['cms::meta' => ['type' => 'cms::meta', 'text' => 'Laravel CMS is outstanding']],
            'status' => 1,
            'editor' => 'seeder',
        ]);
        $page->versions()->create([
            'tenant_id' => 'demo',
            'data' => ['cms::meta' => ['type' => 'cms::meta', 'text' => 'Laravel CMS is outstanding']],
            'published' => true,
            'editor' => 'seeder',
        ]);

        Ref::create([
            'tenant_id' => 'demo',
            'page_id' => $page->id,
            'content_id' => $content->id,
            'position' => 0,
            'status' => 1,
            'editor' => 'seeder',
        ]);

        return $page;
    }


    protected function addBlog( Page $home )
    {
        $page = Page::create([
            'tenant_id' => 'demo',
            'name' => 'Blog',
            'title' => 'Blog | Laravel CMS',
            'slug' => 'blog',
            'tag' => 'blog',
            'status' => 1,
            'editor' => 'seeder',
        ]);
        $page->appendToNode( $home )->save();

        $content = Content::create([
            'tenant_id' => 'demo',
            'data' => ['type' => 'cms::heading', 'text' => 'Blog example'],
            'editor' => 'seeder',
        ]);
        $content->versions()->create([
            'tenant_id' => 'demo',
            'data' => ['type' => 'cms::heading', 'text' => 'Blog example'],
            'published' => true,
            'editor' => 'seeder',
        ]);

        Ref::create([
            'tenant_id' => 'demo',
            'page_id' => $page->id,
            'content_id' => $content->id,
            'position' => 0,
            'status' => 1,
            'editor' => 'seeder',
        ]);


        $content = Content::create([
            'tenant_id' => 'demo',
            'data' => ['type' => 'cms::blog'],
            'editor' => 'seeder',
        ]);
        $content->versions()->create([
            'tenant_id' => 'demo',
            'data' => ['type' => 'cms::blog'],
            'published' => true,
            'editor' => 'seeder',
        ]);

        Ref::create([
            'tenant_id' => 'demo',
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
            'tenant_id' => 'demo',
            'name' => 'Welcome to Laravel CMS',
            'title' => 'Welcome to Laravel CMS | Laravel CMS',
            'slug' => 'welcome-to-laravelcms',
            'tag' => 'article',
            'status' => 1,
            'editor' => 'seeder',
        ]);
        $page->appendToNode( $blog )->save();

        $data = [
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
        ];

        $content = Content::create([
            'tenant_id' => 'demo',
            'data' => $data,
            'editor' => 'seeder',
        ]);
        $version = $content->versions()->create([
            'tenant_id' => 'demo',
            'data' => $data,
            'published' => true,
            'editor' => 'seeder',
        ]);
        $version->files()->attach( $this->file() );

        Ref::create([
            'tenant_id' => 'demo',
            'page_id' => $page->id,
            'content_id' => $content->id,
            'position' => 0,
            'status' => 1,
            'editor' => 'seeder',
        ]);


        $data = ['type' => 'cms::heading', 'level' => 2, 'text' => 'Rethink content management!'];
        $content = Content::create([
            'tenant_id' => 'demo',
            'data' => $data,
            'editor' => 'seeder',
        ]);
        $version = $content->versions()->create([
            'tenant_id' => 'demo',
            'data' => $data,
            'published' => true,
            'editor' => 'seeder',
        ]);

        Ref::create([
            'tenant_id' => 'demo',
            'page_id' => $page->id,
            'content_id' => $content->id,
            'position' => 1,
            'status' => 1,
            'editor' => 'seeder',
        ]);


        $data = ['type' => 'cms::text', 'text' => 'Laravel CMS is exceptional in every way. Headless and API-first!'];
        $content = Content::create([
            'tenant_id' => 'demo',
            'data' => $data,
            'editor' => 'seeder',
        ]);
        $version = $content->versions()->create([
            'tenant_id' => 'demo',
            'data' => $data,
            'published' => true,
            'editor' => 'seeder',
        ]);

        Ref::create([
            'tenant_id' => 'demo',
            'page_id' => $page->id,
            'content_id' => $content->id,
            'position' => 2,
            'status' => 1,
            'editor' => 'seeder',
        ]);


        $data = ['type' => 'cms::heading', 'level' => 2, 'text' => 'API first!'];
        $content = Content::create([
            'tenant_id' => 'demo',
            'data' => $data,
            'editor' => 'seeder',
        ]);
        $version = $content->versions()->create([
            'tenant_id' => 'demo',
            'data' => $data,
            'published' => true,
            'editor' => 'seeder',
        ]);

        Ref::create([
            'tenant_id' => 'demo',
            'page_id' => $page->id,
            'content_id' => $content->id,
            'position' => 3,
            'status' => 1,
            'editor' => 'seeder',
        ]);


        $data = ['type' => 'cms::text', 'text' => 'Use GraphQL for editing the pages, contents and files:'];
        $content = Content::create([
            'tenant_id' => 'demo',
            'data' => $data,
            'editor' => 'seeder',
        ]);
        $version = $content->versions()->create([
            'tenant_id' => 'demo',
            'data' => $data,
            'published' => true,
            'editor' => 'seeder',
        ]);

        Ref::create([
            'tenant_id' => 'demo',
            'page_id' => $page->id,
            'content_id' => $content->id,
            'position' => 4,
            'status' => 1,
            'editor' => 'seeder',
        ]);


        $data = ['type' => 'cms::code', 'language' => 'graphql', 'text' => 'mutation {
  cmsLogin(email: "editor@example.org", password: "secret") {
    name
    email
  }
}'      ];
        $content = Content::create([
            'tenant_id' => 'demo',
            'data' => $data,
            'editor' => 'seeder',
        ]);
        $version = $content->versions()->create([
            'tenant_id' => 'demo',
            'data' => $data,
            'published' => true,
            'editor' => 'seeder',
        ]);

        Ref::create([
            'tenant_id' => 'demo',
            'page_id' => $page->id,
            'content_id' => $content->id,
            'position' => 1,
            'status' => 1,
            'editor' => 'seeder',
        ]);

        return $this;
    }


    protected function addDev( Page $home )
    {
        $page = Page::create([
            'tenant_id' => 'demo',
            'name' => 'Dev',
            'title' => 'For Developer | Laravel CMS',
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
            'tenant_id' => 'demo',
            'data' => $data,
            'editor' => 'seeder',
        ]);
        $content->versions()->create([
            'tenant_id' => 'demo',
            'data' => $data,
            'published' => true,
            'editor' => 'seeder',
        ]);

        Ref::create([
            'tenant_id' => 'demo',
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
            'tenant_id' => 'demo',
            'name' => 'Disabled',
            'title' => 'Disabled page | Laravel CMS',
            'slug' => 'disabled',
            'tag' => 'disabled',
            'status' => 0,
            'editor' => 'seeder',
        ]);
        $page->appendToNode( $home )->save();

        $child = Page::create([
            'tenant_id' => 'demo',
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
        $page = Page::create([
            'tenant_id' => 'demo',
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
