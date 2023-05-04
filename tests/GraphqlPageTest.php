<?php

namespace Tests;

use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Nuwave\Lighthouse\Testing\RefreshesSchemaCache;
use Database\Seeders\CmsSeeder;
use Aimeos\Cms\Models\Content;
use Aimeos\Cms\Models\File;
use Aimeos\Cms\Models\Page;


class GraphqlPageTest extends TestAbstract
{
    use MakesGraphQLRequests;
    use RefreshesSchemaCache;


	protected function defineEnvironment( $app )
	{
        parent::defineEnvironment( $app );

		$app['config']->set( 'lighthouse.schema_path', __DIR__ . '/default-schema.graphql' );
		$app['config']->set( 'lighthouse.namespaces.models', ['App\Models', 'Aimeos\\Cms\\Models'] );
		$app['config']->set( 'lighthouse.namespaces.mutations', ['Aimeos\\Cms\\GraphQL\\Mutations'] );
    }


	protected function getPackageProviders( $app )
	{
		return array_merge( parent::getPackageProviders( $app ), [
			'Nuwave\Lighthouse\LighthouseServiceProvider'
		] );
	}


    protected function setUp(): void
    {
        parent::setUp();
        $this->bootRefreshesSchemaCache();

        $this->user = \App\Models\User::create([
            'name' => 'Test editor',
            'email' => 'editor@testbench',
            'password' => 'secret',
            'cmseditor' => 0x7fffffff
        ]);
    }


    public function testPage()
    {
        $this->seed( CmsSeeder::class );

        $page = Page::where('tag', 'root')->firstOrFail();

        $attr = collect($page->getAttributes())->except(['tenant_id', '_lft', '_rgt'])->all();
        $expected = ['id' => (string) $page->id] + $attr;

        $this->expectsDatabaseQueryCount( 1 );
        $response = $this->actingAs( $this->user )->graphQL( "{
            page(id: {$page->id}) {
                id
                parent_id
                lang
                slug
                name
                title
                domain
                to
                tag
                meta
                data
                config
                status
                cache
                editor
                start
                end
                has
                created_at
                updated_at
                deleted_at
            }
        }" )->assertJson( [
            'data' => [
                'page' => $expected,
            ]
        ] );
    }


    public function testPages()
    {
        $this->seed( CmsSeeder::class );

        $pages = Page::where('tag', 'root')->get();
        $expected = [];

        foreach( $pages as $page )
        {
            $attr = collect($page->getAttributes())->except(['tenant_id', '_lft', '_rgt'])->all();
            $expected[] = ['id' => (string) $page->id] + $attr;
        }

        $this->expectsDatabaseQueryCount( 2 );
        $response = $this->actingAs( $this->user )->graphQL( '{
            pages {
                data {
                    id
                    parent_id
                    lang
                    slug
                    name
                    title
                    domain
                    to
                    tag
                    meta
                    data
                    config
                    status
                    cache
                    editor
                    start
                    end
                    created_at
                    updated_at
                    deleted_at
                }
                paginatorInfo {
                    currentPage
                    lastPage
                }
            }
        }' )->assertJson( [
            'data' => [
                'pages' => [
                    'data' => $expected,
                    'paginatorInfo' => [
                        'currentPage' => 1,
                        'lastPage' => 1,
                    ]
                ],
            ]
        ] );
    }


    public function testPagesWithParentid()
    {
        $this->seed( CmsSeeder::class );

        $root = Page::where('tag', 'root')->firstOrFail();
        $expected = [];

        foreach( $root->children as $page )
        {
            $attr = collect($page->getAttributes())->except(['tenant_id', '_lft', '_rgt'])->all();
            $expected[] = ['id' => (string) $page->id] + $attr;
        }

        $this->expectsDatabaseQueryCount( 2 );
        $response = $this->actingAs( $this->user )->graphQL( '{
            pages(parent_id: "' . $root->id . '", first: 10, page: 1) {
                data {
                    id
                    parent_id
                    lang
                    slug
                    name
                    title
                    domain
                    to
                    tag
                    meta
                    data
                    config
                    status
                    cache
                    editor
                    start
                    end
                    created_at
                    updated_at
                    deleted_at
                }
                paginatorInfo {
                    currentPage
                    lastPage
                }
            }
        }' )->assertJson( [
            'data' => [
                'pages' => [
                    'data' => $expected,
                    'paginatorInfo' => [
                        'currentPage' => 1,
                        'lastPage' => 1,
                    ]
                ],
            ]
        ] );
    }


    public function testPageParent()
    {
        $this->seed( CmsSeeder::class );

        $page = Page::where('tag', 'article')->firstOrFail();

        $this->expectsDatabaseQueryCount( 3 );
        $response = $this->actingAs( $this->user )->graphQL( "{
            page(id: {$page->id}) {
                id
                parent {
                    id
                    tag
                }
            }
        }" )->assertJson( [
            'data' => [
                'page' => [
                    'id' => (string) $page->id,
                    'parent' => [
                        'id' => (string) $page->parent->id,
                        'tag' => 'blog',
                    ]
                ],
            ]
        ] );
    }


    public function testPageChildren()
    {
        $this->seed( CmsSeeder::class );

        $page = Page::where('tag', 'blog')->firstOrFail();

        $this->expectsDatabaseQueryCount( 2 );
        $response = $this->actingAs( $this->user )->graphQL( "{
            page(id: {$page->id}) {
                id
                children(first: 3) {
                    data {
                        tag
                    }
                }
            }
        }" )->assertJson( [
            'data' => [
                'page' => [
                    'id' => (string) $page->id,
                    'children' => [
                        'data' => [
                            ['tag' => 'root'],
                            ['tag' => 'blog'],
                            ['tag' => 'article'],
                        ],
                    ]
                ],
            ]
        ] );
    }


    public function testPageAncestors()
    {
        $this->seed( CmsSeeder::class );

        $page = Page::where('tag', 'article')->firstOrFail();

        $this->expectsDatabaseQueryCount( 2 );
        $response = $this->actingAs( $this->user )->graphQL( "{
            page(id: {$page->id}) {
                id
                ancestors {
                    tag
                }
            }
        }" )->assertJson( [
            'data' => [
                'page' => [
                    'id' => (string) $page->id,
                    'ancestors' => [
                        ['tag' => 'root'],
                        ['tag' => 'blog'],
                    ]
                ],
            ]
        ] );
    }


    public function testPageVersions()
    {
        $this->seed( CmsSeeder::class );

        $page = Page::where('tag', 'root')->firstOrFail();

        $this->expectsDatabaseQueryCount( 2 );
        $response = $this->actingAs( $this->user )->graphQL( "{
            page(id: {$page->id}) {
                id
                versions {
                    meta
                    data
                    editor
                }
            }
        }" )->assertJson( [
            'data' => [
                'page' => [
                    'id' => (string) $page->id,
                    'versions' => [
                        [
                            'meta' => '{"cms::meta":{"type":"cms::meta","text":"Laravel CMS is outstanding"}}',
                            'data' => '[{"type":"cms::heading","text":"Welcome to Laravel CMS"}]',
                            'editor' => 'seeder'
                        ],
                    ],
                ],
            ]
        ] );
    }


    public function testPageSimple()
    {
        $this->seed( CmsSeeder::class );

        $page = Page::where('tag', 'disabled')->firstOrFail();

        $this->expectsDatabaseQueryCount( 5 );
        $response = $this->actingAs( $this->user )->graphQL( "{
            page(id: {$page->id}) {
                id
                ancestors {
                    id
                }
                children(first: 2) {
                    data {
                        id
                    }
                }
                contents {
                    id
                }
                versions {
                    id
                }
            }
        }" )->assertJson( [
            'data' => [
                'page' => [
                    'id' => (string) $page->id,
                    'ancestors' => [],
                    'children' => [],
                    'contents' => [],
                    'versions' => [],
                ],
            ]
        ] );
    }


    public function testPageContents()
    {
        $this->seed( CmsSeeder::class );

        $page = Page::where('tag', 'root')->firstOrFail();

        $this->expectsDatabaseQueryCount( 2 );
        $response = $this->actingAs( $this->user )->graphQL( "{
            page(id: {$page->id}) {
                id
                contents {
                    lang
                    label
                    data
                }
            }
        }" )->assertJson( [
            'data' => [
                'page' => [
                    'id' => (string) $page->id,
                    'contents' => [
                        [
                            'lang' => '',
                            'label' => 'Test shared content',
                            'data' => '{"type":"cms::heading","text":"Welcome to Laravel CMS"}',
                        ],
                    ],
                ],
            ]
        ] );
    }


    public function testAddPage()
    {
        $this->seed( CmsSeeder::class );

        $file = File::firstOrFail();
        $content = Content::firstOrFail();

        $this->expectsDatabaseQueryCount( 8 );
        $response = $this->actingAs( $this->user )->graphQL( '
            mutation {
                addPage(input: {
                    lang: "en"
                    slug: "test"
                    name: "test"
                    domain: "test.com"
                    title: "Test page"
                    to: "/to/page"
                    tag: "test"
                    data: "[{\"type\":\"cms::heading\",\"text\":\"Welcome to Laravel CMS\"}]",
                    meta: "{\"canonical\":\"to\/page\"}"
                    config: "{\"key\":\"test\"}"
                    status: 0
                    cache: 0
                    start: "2023-01-01 00:00:00"
                    end: "2099-01-01 00:00:00"
                    contents: ["' . $content->id . '"]
                    files: ["' . $file->id . '"]
                }) {
                    id
                    parent_id
                    lang
                    slug
                    domain
                    name
                    title
                    to
                    tag
                    meta
                    data
                    config
                    status
                    cache
                    editor
                    start
                    end
                    created_at
                    updated_at
                    deleted_at
                    contents {
                        lang
                        data
                        label
                    }
                }
            }
        ' );

        $page = Page::where('tag', 'test')->where('lang', 'en')->firstOrFail();

        $attr = collect($page->getAttributes())->except(['tenant_id', '_lft', '_rgt'])->all();
        $expected = ['id' => (string) $page->id, 'parent_id' => null] + $attr;
        $expected['data'] = '[]'; // status is 0, so not yet published

        $response->assertJson( [
            'data' => [
                'addPage' => $expected,
            ]
        ] );
    }


    public function testAddPageChild()
    {
        $this->seed( CmsSeeder::class );

        $root = Page::where('tag', 'root')->firstOrFail();

        $this->expectsDatabaseQueryCount( 7 );
        $response = $this->actingAs( $this->user )->graphQL( '
            mutation {
                addPage(input: {
                    lang: "en"
                    slug: "test"
                    name: "test"
                    domain: "test.com"
                    title: "Test page"
                    to: "/to/page"
                    tag: "test"
                    meta: "{}"
                    data: "{}"
                    config: "{}"
                    status: 0
                    cache: 0
                }, parent: "' . $root->id . '") {
                    id
                    parent_id
                }
            }
        ' );

        $page = Page::where('tag', 'test')->firstOrFail();

        $response->assertJson( [
            'data' => [
                'addPage' => ['id' => (string) $page->id, 'parent_id' => $root->id],
            ]
        ] );
    }


    public function testAddPageChildRef()
    {
        $this->seed( CmsSeeder::class );

        $root = Page::where('tag', 'root')->firstOrFail();
        $ref = Page::where('tag', 'blog')->firstOrFail();

        $this->expectsDatabaseQueryCount( 7 );
        $response = $this->actingAs( $this->user )->graphQL( '
            mutation {
                addPage(input: {
                    lang: "en"
                    slug: "test"
                    name: "test"
                    domain: ""
                    title: "Test page"
                    to: "/to/page"
                    tag: "test"
                    meta: "{}"
                    data: "{}"
                    config: "{}"
                    status: 0
                    cache: 0
                }, parent: "' . $root->id . '", ref: "' . $ref->id .'") {
                    id
                    parent_id
                }
            }
        ' );

        $page = Page::where('tag', 'test')->firstOrFail();

        $response->assertJson( [
            'data' => [
                'addPage' => ['id' => (string) $page->id, 'parent_id' => $root->id],
            ]
        ] );
        $this->assertEquals( 2, $page->_lft );
        $this->assertEquals( 3, $page->_rgt );
    }


    public function testMovePage()
    {
        $this->seed( CmsSeeder::class );

        $blog = Page::where('tag', 'blog')->firstOrFail();

        $this->expectsDatabaseQueryCount( 7 );
        $response = $this->actingAs( $this->user )->graphQL( '
            mutation {
                movePage(id: "' . $blog->id . '") {
                    id
                    parent_id
                    editor
                }
            }
        ' );

        $page = Page::where('tag', 'blog')->firstOrFail();

        $response->assertJson( [
            'data' => [
                'movePage' => [
                    'id' => (string) $page->id,
                    'parent_id' => null,
                    'editor' => 'Test editor',
                ],
            ]
        ] );
    }


    public function testMovePageParent()
    {
        $this->seed( CmsSeeder::class );

        $root = Page::where('tag', 'root')->firstOrFail();
        $article = Page::where('tag', 'article')->firstOrFail();

        $this->expectsDatabaseQueryCount( 9 );
        $response = $this->actingAs( $this->user )->graphQL( '
            mutation {
                movePage(id: "' . $article->id . '", parent: "' . $root->id . '") {
                    id
                    parent_id
                }
            }
        ' );

        $page = Page::where('tag', 'article')->firstOrFail();

        $response->assertJson( [
            'data' => [
                'movePage' => [
                    'id' => (string) $page->id,
                    'parent_id' => $root->id
                ],
            ]
        ] );
    }


    public function testMovePageParentRef()
    {
        $this->seed( CmsSeeder::class );

        $root = Page::where('tag', 'root')->firstOrFail();
        $blog = Page::where('tag', 'blog')->firstOrFail();
        $article = Page::where('tag', 'article')->firstOrFail();

        $this->expectsDatabaseQueryCount( 9 );
        $response = $this->actingAs( $this->user )->graphQL( '
            mutation {
                movePage(id: "' . $article->id . '", parent: "' . $root->id . '", ref: "' . $blog->id . '") {
                    id
                    parent_id
                }
            }
        ' );

        $page = Page::where('tag', 'article')->firstOrFail();

        $response->assertJson( [
            'data' => [
                'movePage' => [
                    'id' => (string) $page->id,
                    'parent_id' => $root->id
                ],
            ]
        ] );
        $this->assertEquals( 2, $page->_lft );
        $this->assertEquals( 3, $page->_rgt );
    }


    public function testSavePage()
    {
        $this->seed( CmsSeeder::class );

        $file = File::firstOrFail();
        $content = Content::firstOrFail();
        $root = Page::where('tag', 'root')->firstOrFail();

        $this->expectsDatabaseQueryCount( 13 );
        $response = $this->actingAs( $this->user )->graphQL( '
            mutation {
                savePage(id: "' . $root->id . '", input: {
                    lang: "en"
                    slug: "test"
                    domain: "test.com"
                    name: "test"
                    title: "Test page"
                    to: "/to/page"
                    tag: "test"
                    meta: "{\"canonical\":\"to\/page\"}"
                    data: "[{\"type\":\"cms::heading\",\"text\":\"Welcome to Laravel CMS\"}]",
                    config: "{\"key\":\"test\"}"
                    status: 0
                    cache: 5
                    start: "2023-01-01 00:00:00"
                    end: "2099-01-01 00:00:00"
                    contents: ["' . $content->id . '"]
                    files: ["' . $file->id . '"]
                }) {
                    id
                    parent_id
                    lang
                    slug
                    domain
                    name
                    title
                    to
                    tag
                    meta
                    data
                    config
                    status
                    cache
                    editor
                    start
                    end
                    contents {
                        lang
                        data
                        label
                    }
                    created_at
                    updated_at
                    deleted_at
                    latest {
                        data
                    }
                    published {
                        data
                    }
                }
            }
        ' );

        $page = Page::where('id', $root->id)->firstOrFail();

        $response->assertJson( [
            'data' => [
                'savePage' => [
                    'id' => (string) $root->id,
                    'parent_id' => null,
                    'lang' => "en",
                    'slug' => "test",
                    'domain' => 'test.com',
                    'name' => "test",
                    'title' => "Test page",
                    'to' => "/to/page",
                    'tag' => "test",
                    'meta' => '{"cms::meta":{"type":"cms::meta","text":"Laravel CMS is outstanding"}}',
                    'data' => '[{"type":"cms::heading","text":"Welcome to Laravel CMS"}]',
                    'config' => '{"key":"test"}',
                    'status' => 0,
                    'cache' => 5,
                    'editor' => 'Test editor',
                    'start' => '2023-01-01 00:00:00',
                    'end' => '2099-01-01 00:00:00',
                    'created_at' => (string) $root->created_at,
                    'updated_at' => (string) $page->updated_at,
                    'latest' => [
                        'data' => '[{"type":"cms::heading","text":"Welcome to Laravel CMS"}]'
                    ],
                    'published' => [
                        'data' => '[{"type":"cms::heading","text":"Welcome to Laravel CMS"}]'
                    ]
                ],
            ]
        ] );
    }


    public function testDropPage()
    {
        $this->seed( CmsSeeder::class );

        $root = Page::where('tag', 'root')->firstOrFail();

        $this->expectsDatabaseQueryCount( 6 );
        $response = $this->actingAs( $this->user )->graphQL( '
            mutation {
                dropPage(id: "' . $root->id . '") {
                    id
                    editor
                    deleted_at
                }
            }
        ' );

        $page = Page::withTrashed()->where('id', $root->id)->firstOrFail();

        $response->assertJson( [
            'data' => [
                'dropPage' => [
                    'id' => (string) $root->id,
                    'editor' => 'Test editor',
                    'deleted_at' => (string) $page->deleted_at,
                ],
            ]
        ] );

        foreach( $page->children as $child ) {
            $this->assertNotNull( $child->deleted_at );
        }
    }


    public function testKeepPage()
    {
        $this->seed( CmsSeeder::class );

        $root = Page::where('tag', 'root')->firstOrFail();
        $root->delete();

        $this->expectsDatabaseQueryCount( 5 );
        $response = $this->actingAs( $this->user )->graphQL( '
            mutation {
                keepPage(id: "' . $root->id . '") {
                    id
                    editor
                    deleted_at
                }
            }
        ' );

        $page = Page::where('id', $root->id)->firstOrFail();

        $response->assertJson( [
            'data' => [
                'keepPage' => [
                    'id' => (string) $root->id,
                    'editor' => 'Test editor',
                    'deleted_at' => null,
                ],
            ]
        ] );

        foreach( $page->children as $child ) {
            $this->assertNull( $child->deleted_at );
        }
    }


    public function testPubPage()
    {
        $this->seed( CmsSeeder::class );

        $page = Page::where('tag', 'root')->firstOrFail();

        $this->expectsDatabaseQueryCount( 5 );
        $response = $this->actingAs( $this->user )->graphQL( '
            mutation {
                pubPage(id: "' . $page->id . '") {
                    id
                }
            }
        ' );

        $page = Page::where('id', $page->id)->firstOrFail();

        $response->assertJson( [
            'data' => [
                'pubPage' => [
                    'id' => (string) $page->id
                ],
            ]
        ] );
    }


    public function testPurgePage()
    {
        $this->seed( CmsSeeder::class );

        $root = Page::where('tag', 'root')->firstOrFail();

        $this->expectsDatabaseQueryCount( 6 );
        $response = $this->actingAs( $this->user )->graphQL( '
            mutation {
                purgePage(id: "' . $root->id . '") {
                    id
                }
            }
        ' );

        $this->assertNull( Page::where('tag', 'root')->first() );
    }
}
