<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use LaravelJsonApi\Testing\MakesJsonApiRequests;


class JsonApiTest extends \Orchestra\Testbench\TestCase
{
    use RefreshDatabase;
	use MakesJsonApiRequests;

    protected $enablesPackageDiscoveries = true;


    protected function defineDatabaseMigrations()
    {
		$this->loadLaravelMigrations();
        $this->loadMigrationsFrom(__DIR__ . '/migrations/jsonapi');
    }


	protected function defineEnvironment( $app )
	{
		$app['config']->set( 'cms.db', 'testing' );
		$app['config']->set( 'jsonapi.servers.cms', \Aimeos\Cms\JsonApi\V1\Server::class );
	}


	protected function defineRoutes( $router )
	{
        \LaravelJsonApi\Laravel\Facades\JsonApiRoute::server("cms")->prefix("cms")->resources(function($server) {
            $server->resource("pages", \LaravelJsonApi\Laravel\Http\Controllers\JsonApiController::class)->readOnly()
                ->relationships(function ($relationships) {
                    $relationships->hasOne('content')->readOnly();
                });
        });
	}


	protected function getPackageProviders( $app )
	{
		return [
			'Aimeos\Cms\CmsServiceProvider',
			'Kalnoy\Nestedset\NestedSetServiceProvider',
			'LaravelJsonApi\Laravel\ServiceProvider'
		];
	}


    public function testPages()
    {
        $this->seed( \Database\Seeders\CmsSeeder::class );

        $pages = \Aimeos\Cms\Models\Page::where('tag', 'root')->get();

        $response = $this->jsonApi()->expects( 'pages' )->get( 'cms/pages' );
        $response->assertFetchedMany( $pages );

        $this->assertGreaterThanOrEqual( 1, count( $pages ) );
    }


    public function testPagesFilter()
    {
        $this->seed( \Database\Seeders\CmsSeeder::class );

        $pages = \Aimeos\Cms\Models\Page::where('tag', 'root')->get();

        $response = $this->jsonApi()->expects( 'pages' )->filter( ['tag' => 'root', 'lang' => ''] )->get( "cms/pages" );
        $response->assertFetchedMany( $pages );
    }


    public function testPage()
    {
        $this->seed( \Database\Seeders\CmsSeeder::class );

        $page = \Aimeos\Cms\Models\Page::where('tag', 'root')->firstOrFail();

        $response = $this->jsonApi()->expects( 'pages' )->get( "cms/pages/{$page->id}" );
        $response->assertFetchedOne( $page );
    }


    public function testPageContent()
    {
        $this->seed( \Database\Seeders\CmsSeeder::class );

        $page = \Aimeos\Cms\Models\Page::where('tag', 'blog')->firstOrFail();

        $response = $this->jsonApi()->expects( 'contents' )->get( "cms/pages/{$page->id}/content" );
        $response->assertFetchedManyInOrder( $page->content );

        $this->assertGreaterThanOrEqual( 2, count( $page->content ) );
    }


    public function testPageRelationshipsContent()
    {
        $this->seed( \Database\Seeders\CmsSeeder::class );

        $page = \Aimeos\Cms\Models\Page::where('tag', 'blog')->firstOrFail();

        $response = $this->jsonApi()->expects( 'contents' )->get( "cms/pages/{$page->id}/relationships/content" );
        $response->assertFetchedToManyInOrder( $page->content );

        $this->assertGreaterThanOrEqual( 2, count( $page->content ) );
    }


    public function testPageIncludeAncestors()
    {
        $this->seed( \Database\Seeders\CmsSeeder::class );

        $page = \Aimeos\Cms\Models\Page::where('tag', 'article')->firstOrFail();
        $expected = [];

        foreach( $page->ancestors as $item ){
            $expected[] = ['type' => 'pages', 'id' => $item->id];
        }

        $response = $this->jsonApi()->expects( 'pages' )->includePaths( 'ancestors' )->get( "cms/pages/{$page->id}" );
        $response->assertFetchedOne( $page )->assertIncluded( $expected );

        $this->assertEquals( 2, count( $expected ) );
    }


    public function testPageIncludeContent()
    {
        $this->seed( \Database\Seeders\CmsSeeder::class );

        $page = \Aimeos\Cms\Models\Page::where('tag', 'root')->firstOrFail();
        $expected = [];

        foreach( $page->content as $item ){
            $expected[] = ['type' => 'contents', 'id' => $item->id];
        }

        $response = $this->jsonApi()->expects( 'pages' )->includePaths( 'content' )->get( "cms/pages/{$page->id}" );
        $response->assertFetchedOne( $page )->assertIncluded( $expected );

        $this->assertGreaterThanOrEqual( 1, count( $expected ) );
    }


    public function testPageIncludeChildren()
    {
        $this->seed( \Database\Seeders\CmsSeeder::class );

        $page = \Aimeos\Cms\Models\Page::where('tag', 'root')->firstOrFail();
        $expected = [];

        foreach( $page->children->filter( fn($item) => $item->status > 0 ) as $item ) {
            $expected[] = ['type' => 'pages', 'id' => $item->id];
        }

        $response = $this->jsonApi()->expects( 'pages' )->includePaths( 'children' )->get( "cms/pages/{$page->id}" );
        $response->assertFetchedOne( $page )->assertIncluded( $expected );

        $this->assertGreaterThanOrEqual( 3, count( $expected ) );
    }


    public function testPageIncludeChildrenOfChildren()
    {
        $this->seed( \Database\Seeders\CmsSeeder::class );

        $page = \Aimeos\Cms\Models\Page::where('tag', 'root')->firstOrFail();
        $expected = [];

        foreach( $page->children->filter( fn($item) => $item->status > 0 ) as $item )
        {
            $expected[] = ['type' => 'pages', 'id' => $item->id];

            foreach( $item->children->filter( fn($item) => $item->status > 0 ) as $subItem ) {
                $expected[] = ['type' => 'pages', 'id' => $subItem->id];
            }
        }

        $response = $this->jsonApi()->expects( 'pages' )->includePaths( 'children.children' )->get( "cms/pages/{$page->id}" );
        $response->assertFetchedOne( $page )->assertIncluded( $expected );

        $this->assertGreaterThanOrEqual( 4, count( $expected ) );
    }


    public function testPageIncludeDescendents()
    {
        $this->seed( \Database\Seeders\CmsSeeder::class );

        $page = \Aimeos\Cms\Models\Page::where('tag', 'root')->firstOrFail();
        $expected = [];

        foreach( $page->descendants->filter( fn($item) => $item->status > 0 ) as $item ){
            $expected[] = ['type' => 'pages', 'id' => $item->id];
        }

        $response = $this->jsonApi()->expects( 'pages' )->includePaths( 'descendants' )->get( "cms/pages/{$page->id}" );
        $response->assertFetchedOne( $page )->assertIncluded( $expected );

        $this->assertEquals( 4, count( $expected ) );
    }


    public function testPageIncludeParent()
    {
        $this->seed( \Database\Seeders\CmsSeeder::class );

        $page = \Aimeos\Cms\Models\Page::where('tag', 'article')->firstOrFail();

        $response = $this->jsonApi()->expects( 'pages' )->includePaths( 'parent' )->get( "cms/pages/{$page->id}" );
        $response->assertFetchedOne( $page )->assertIsIncluded( 'pages', $page->parent );
    }


    public function testPageDisabled()
    {
        $this->seed( \Database\Seeders\CmsSeeder::class );

        $page = \Aimeos\Cms\Models\Page::where('tag', 'disabled')->firstOrFail();

        $response = $this->jsonApi()->expects( 'pages' )->get( "cms/pages/{$page->id}" );
        $response->assertNotFound();
    }


    public function testPageDisabledParent()
    {
        $this->seed( \Database\Seeders\CmsSeeder::class );

        $page = \Aimeos\Cms\Models\Page::where('tag', 'disabled-child')->firstOrFail();

        $response = $this->jsonApi()->expects( 'pages' )->includePaths( 'parent' )->get( "cms/pages/{$page->id}" );
        $response->assertFetchedOne( $page )->assertDoesntHaveIncluded();
    }


    public function testPageHidden()
    {
        $this->seed( \Database\Seeders\CmsSeeder::class );

        $page = \Aimeos\Cms\Models\Page::where('tag', 'hidden')->firstOrFail();

        $response = $this->jsonApi()->expects( 'pages' )->get( "cms/pages/{$page->id}" );
        $response->assertFetchedOne( $page );
    }
}
