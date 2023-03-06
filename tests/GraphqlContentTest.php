<?php

namespace Tests;

use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Nuwave\Lighthouse\Testing\RefreshesSchemaCache;
use Database\Seeders\CmsSeeder;
use Aimeos\Cms\Models\Content;
use Aimeos\Cms\Models\File;
use Aimeos\Cms\Models\Page;


class GraphqlContentTest extends TestAbstract
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
            'name' => 'Test',
            'email' => 'editor@testbench',
            'password' => 'secret',
            'cmseditor' => 1
        ]);
    }


    public function testContent()
    {
        $this->seed( CmsSeeder::class );

        $content = Content::firstOrFail();

        $attr = collect($content->getAttributes())->except(['tenant_id'])->all();
        $expected = ['id' => (string) $content->id] + $attr;

        $this->expectsDatabaseQueryCount( 1 );
        $response = $this->actingAs( $this->user )->graphQL( "{
            content(id: \"{$content->id}\") {
                id
                lang
                data
                editor
                created_at
                updated_at
                deleted_at
            }
        }" )->assertJson( [
            'data' => [
                'content' => $expected,
            ]
        ] );
    }


    public function testContents()
    {
        $this->seed( CmsSeeder::class );

        $contents = Content::limit( 10 )->get();
        $expected = [];

        foreach( $contents as $content )
        {
            $attr = collect($content->getAttributes())->except(['tenant_id'])->all();
            $expected[] = ['id' => (string) $content->id] + $attr;
        }

        $this->expectsDatabaseQueryCount( 2 );
        $response = $this->actingAs( $this->user )->graphQL( '{
            contents(first: 10, page: 1) {
                data {
                    id
                    lang
                    data
                    editor
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
                'contents' => [
                    'data' => $expected,
                    'paginatorInfo' => [
                        'currentPage' => 1,
                        'lastPage' => 1,
                    ]
                ],
            ]
        ] );
    }


    public function testAddContent()
    {
        $this->seed( CmsSeeder::class );

        $file = File::firstOrFail();

        $this->expectsDatabaseQueryCount( 5 );
        $response = $this->actingAs( $this->user )->graphQL( '
            mutation {
                addContent(input: {
                    lang: "en"
                    data: "{\\"key\\":\\"value\\"}"
                }, files: ["' . $file->id . '"]) {
                    lang
                    data
                    editor
                    pages {
                        id
                    }
                    files {
                        name
                    }
                }
            }
        ' );

        $response->assertJson( [
            'data' => [
                'addContent' => [
                    'lang' => 'en',
                    'data' => '{"key":"value"}',
                    'editor' => 'Test',
                    'pages' => [],
                    'files' => [
                        ['name' => 'Test image']
                    ]
                ],
            ]
        ] );
    }


    public function testAddContentToPage()
    {
        $this->seed( CmsSeeder::class );

        $root = Page::where('tag', 'root')->firstOrFail();
        $contents = $root->contents;

        $this->expectsDatabaseQueryCount( 6 );
        $response = $this->actingAs( $this->user )->graphQL( '
            mutation {
                addContent(input: {
                    lang: "en"
                    data: "{\\"key\\":\\"value\\"}"
                }, page_id: "' . $root->id . '", position: 1) {
                    lang
                    data
                    editor
                    pages {
                        tag
                        ref {
                            position
                            status
                        }
                    }
                }
            }
        ' );

        $response->assertJson( [
            'data' => [
                'addContent' => [
                    'lang' => 'en',
                    'data' => '{"key":"value"}',
                    'editor' => 'Test',
                    'pages' => [
                        [
                            'tag' => 'root',
                            'ref' => [
                                'position' => 1,
                                'status' => 0,
                            ]
                        ]
                    ],
                ],
            ]
        ] );
        $this->assertEquals( $contents->count() + 1, Page::where('tag', 'root')->first()?->contents->count() );
    }


    public function testSaveContent()
    {
        $this->seed( CmsSeeder::class );

        $file = File::firstOrFail();
        $content = Content::firstOrFail();

        $this->expectsDatabaseQueryCount( 6 );
        $response = $this->actingAs( $this->user )->graphQL( '
            mutation {
                saveContent(id: "' . $content->id . '", input: {
                    lang: "en"
                    data: "{\\"key\\":\\"value\\"}"
                }, files: ["' . $file->id . '"]) {
                    id
                    lang
                    data
                    editor
                    files {
                        name
                    }
                }
            }
        ' );

        $content = Content::find( $content->id );

        $response->assertJson( [
            'data' => [
                'saveContent' => [
                    'id' => $content->id,
                    'lang' => 'en',
                    'data' => '{"key":"value"}',
                    'editor' => 'Test',
                    'files' => [
                        ['name' => 'Test image']
                    ]
               ],
            ]
        ] );
    }


    public function testDropContent()
    {
        $this->seed( CmsSeeder::class );

        $content = Content::firstOrFail();

        $this->expectsDatabaseQueryCount( 3 );
        $response = $this->actingAs( $this->user )->graphQL( '
            mutation {
                dropContent(id: "' . $content->id . '") {
                    id
                    deleted_at
                }
            }
        ' );

        $content = Content::withTrashed()->find( $content->id );

        $response->assertJson( [
            'data' => [
                'dropContent' => [
                    'id' => $content->id,
                    'deleted_at' => $content->deleted_at,
                ],
            ]
        ] );
    }


    public function testKeepContent()
    {
        $this->seed( CmsSeeder::class );

        $content = Content::firstOrFail();
        $content->delete();

        $this->expectsDatabaseQueryCount( 3 );
        $response = $this->actingAs( $this->user )->graphQL( '
            mutation {
                keepContent(id: "' . $content->id . '") {
                    id
                    deleted_at
                }
            }
        ' );

        $content = Content::find( $content->id );

        $response->assertJson( [
            'data' => [
                'keepContent' => [
                    'id' => $content->id,
                    'deleted_at' => null,
                ],
            ]
        ] );
    }
}
