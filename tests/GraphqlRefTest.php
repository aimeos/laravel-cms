<?php

namespace Tests;

use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Nuwave\Lighthouse\Testing\RefreshesSchemaCache;
use Database\Seeders\CmsSeeder;
use Aimeos\Cms\Models\Page;
use Aimeos\Cms\Models\Ref;


class GraphqlRefTest extends TestAbstract
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


    public function testAddRef()
    {
        $this->seed( CmsSeeder::class );

        $root = Page::where('tag', 'root')->firstOrFail();
        $page = Page::where('tag', 'blog')->firstOrFail();
        $content = $root->contents->first();

        $this->expectsDatabaseQueryCount( 3 );
        $response = $this->actingAs( $this->user )->graphQL( "
            mutation {
                addRef(input: {
                    page_id: \"{$page->id}\"
                    content_id: \"{$content->id}\"
                    position: 10
                    status: 1
                }) {
                    page_id
                    content_id
                    position
                    status
                    editor
                }
            }
        " );

        $page = Page::where('tag', 'blog')->firstOrFail();
        $content = $page->contents->last();

        $response->assertJson( [
            'data' => [
                'addRef' => [
                    'page_id' => (string) $page->id,
                    'content_id' => $content->id,
                    'position' => 10,
                    'status' => 1,
                    'editor' => 'Test',
                ],
            ]
        ] );
    }


    public function testSaveRef()
    {
        $this->seed( CmsSeeder::class );

        $root = Page::where('tag', 'root')->firstOrFail();
        $content = $root->contents->first();

        $this->expectsDatabaseQueryCount( 5 );
        $response = $this->actingAs( $this->user )->graphQL( "
            mutation {
                saveRef(id: \"{$content->ref->id}\", input: {
                    position: 10
                    status: 0
                }) {
                    position
                    status
                    editor
                }
            }
        " );

        $page = Page::where('tag', 'root')->firstOrFail();
        $content = $page->contents->first();

        $response->assertJson( [
            'data' => [
                'saveRef' => [
                    'position' => 10,
                    'status' => 0,
                    'editor' => 'Test',
                ],
            ]
        ] );
    }


    public function testDropRef()
    {
        $this->seed( CmsSeeder::class );

        $root = Page::where('tag', 'root')->firstOrFail();
        $contents = $root->contents;
        $content = $contents->first();

        $this->expectsDatabaseQueryCount( 5 );
        $response = $this->actingAs( $this->user )->graphQL( "
            mutation {
                dropRef(id: \"{$content->ref->id}\") {
                    id
                    page_id
                    content_id
                }
            }
        " );

        $page = Page::where('tag', 'root')->firstOrFail();
        $this->assertEquals( $contents->count() - 1, $page->contents->count() );

        $response->assertJson( [
            'data' => [
                'dropRef' => [
                    'id' => $content->ref->id,
                    'page_id' => $content->ref->page_id,
                    'content_id' => $content->ref->content_id,
                ],
            ]
        ] );
    }
}
