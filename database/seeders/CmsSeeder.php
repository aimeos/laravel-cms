<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use Aimeos\Cms\Models\Version;
use Aimeos\Cms\Models\Element;
use Aimeos\Cms\Models\File;
use Aimeos\Cms\Models\Page;


class CmsSeeder extends Seeder
{
    private string $shared;
    private array $file;


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
        Element::where('tenant_id', 'demo')->forceDelete();
        Page::where('tenant_id', 'demo')->forceDelete();

        $home = $this->home();

        $this->addBlog( $home )
            ->addDev( $home )
            ->addHidden( $home )
            ->addDisabled( $home );
    }


    protected function file() : array
    {
        if( !isset( $this->file ) )
        {
            $this->file = [
                'mime' => 'image/jpeg',
                'tag' => 'test',
                'name' => 'Test image',
                'path' => 'https://picsum.photos/id/0/1500/1000',
                'previews' => ["1000" => "https://picsum.photos/id/0/1000/666", "500" => "https://picsum.photos/id/0/500/333"],
                'editor' => 'seeder',
            ];

            $file = File::forceCreate( $this->file );
            $this->file['id'] = $file->id;
        }

        return $this->file;
    }


    protected function shared() : string
    {
        if( !isset( $this->shared ) )
        {
            $data = ['type' => 'footer', 'data' => ['text' => 'Powered by Laravel CMS']];

            $element = Element::forceCreate([
                'label' => 'Shared footer',
                'data' => ['type' => 'footer', 'data' => ['text' => 'Powered by Laravel CMS']],
                'editor' => 'seeder',
            ]);

            $version = $element->versions()->forceCreate([
                'data' => ['type' => 'footer', 'data' => ['text' => 'Powered by Laravel CMS!']],
                'publish_at' => '2025-01-01 00:00:00',
                'editor' => 'seeder',
            ]);

            $this->shared = $element->id;
        }

        return $this->shared;
    }


    protected function home() : Page
    {
        $sharedId = $this->shared();

        $page = Page::forceCreate([
            'name' => 'Home',
            'title' => 'Home | Laravel CMS',
            'slug' => '',
            'tag' => 'root',
            'domain' => 'mydomain.tld',
            'status' => 1,
            'editor' => 'seeder',
            'meta' => ['meta' => ['type' => 'meta', 'data' => ['text' => 'Laravel CMS is outstanding']]],
            'contents' => [
                ['type' => 'heading', 'text' => 'Welcome to Laravel CMS'],
                ['type' => 'ref', 'id' => $sharedId]
            ],
        ]);
        $page->versions()->forceCreate([
            'data' => [
                'name' => 'Home',
                'title' => 'Home | Laravel CMS',
                'slug' => '',
                'tag' => 'root',
                'domain' => 'mydomain.tld',
                'status' => 1,
                'editor' => 'seeder',
                'meta' => ['meta' => ['type' => 'meta', 'text' => 'Laravel CMS is outstanding']],
                'contents' => [
                    ['type' => 'heading', 'text' => 'Welcome to Laravel CMS'],
                    ['type' => 'ref', 'id' => $sharedId]
                ],
            ],
            'published' => true,
            'editor' => 'seeder',
        ]);
        $page->elements()->attach( $sharedId );

        return $page;
    }


    protected function addBlog( Page $home )
    {
        $sharedId = $this->shared();

        $page = Page::forceCreate([
            'name' => 'Blog',
            'title' => 'Blog | Laravel CMS',
            'slug' => 'blog',
            'tag' => 'blog',
            'status' => 1,
            'editor' => 'seeder',
            'contents' => [
                ['type' => 'blog', 'data' => ['text' => 'Blog example']],
                ['type' => 'ref', 'id' => $sharedId]
            ],
        ]);
        $page->appendToNode( $home )->save();
        $page->elements()->attach( $sharedId );

        return $this->addBlogArticle( $page );
    }


    protected function addBlogArticle( Page $blog )
    {
        $sharedId = $this->shared();
        $file = $this->file();

        $data = [
            [
                'type' => 'article',
                'data' => [
                    'title' => 'Welcome to Laravel CMS',
                    'cover' => $file,
                    'intro' => 'A new light-weight Laravel CMS is here!',
                    'text' => 'Laravel CMS is lightweight, lighting fast, easy to use, fully customizable and scalable from one-pagers to millions of pages',
                ]
            ],
            ['type' => 'heading', 'data' => ['level' => 2, 'text' => 'Rethink content management!']],
            ['type' => 'paragraph', 'data' => ['text' => 'Laravel CMS is exceptional in every way. Headless and API-first!']],
            ['type' => 'heading', 'data' => ['level' => 2, 'text' => 'API first!']],
            ['type' => 'paragraph', 'data' => [
                'text' => 'Use GraphQL for editing everything after login:

```graphql
mutation {
  cmsLogin(email: "editor@example.org", password: "secret") {
    name
    email
  }
}
```'            ],
            ],
            ['type' => 'ref', 'id' => $sharedId],
        ];

        $page = Page::forceCreate([
            'name' => 'Welcome to Laravel CMS',
            'title' => 'Welcome to Laravel CMS | Laravel CMS',
            'slug' => 'welcome-to-laravelcms',
            'tag' => 'article',
            'status' => 1,
            'editor' => 'seeder',
            'contents' => $data
        ]);
        $page->appendToNode( $blog )->save();
        $page->elements()->attach( $sharedId );

        $version = $page->versions()->forceCreate([
            'data' => $data,
            'published' => true,
            'editor' => 'seeder',
        ]);
        $version->files()->attach( $file['id'] );

        return $this;
    }


    protected function addDev( Page $home )
    {
        $sharedId = $this->shared();

        $page = Page::forceCreate([
            'name' => 'Dev',
            'title' => 'For Developer | Laravel CMS',
            'slug' => 'dev',
            'status' => 1,
            'editor' => 'seeder',
            'contents' => [[
                'type' => 'paragraph',
                'data' => [
                    'text' => '# For Developers

This is content created using [markdown syntax](https://www.markdownguide.org/basic-syntax/)'
                ]
            ], [
                'type' => 'image-text',
                'data' => [
                    'image' => $this->file(),
                    'text' => 'Test image'
                ]
            ], [
                'type' => 'ref', 'id' => $sharedId
            ]]
        ]);
        $page->appendToNode( $home )->save();
        $page->elements()->attach( $sharedId );

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

        $version = $page->versions()->forceCreate([
            'data' => [
                'name' => 'Hidden',
                'title' => 'Hidden page | Laravel CMS',
                'slug' => 'hidden',
                'tag' => 'hidden',
                'status' => 1,
                'editor' => 'seeder',
            ],
            'publish_at' => '2025-01-01 00:00:00',
            'published' => false,
            'editor' => 'seeder',
        ]);

        return $this;
    }
}
