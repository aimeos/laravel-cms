<?php

namespace Tests;

use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Nuwave\Lighthouse\Testing\RefreshesSchemaCache;
use Prism\Prism\Testing\TextResponseFake;
use Prism\Prism\Prism;


class GraphqlTest extends TestAbstract
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


    public function testCompose()
    {
        $expected = 'Generated content based on the prompt.';
        Prism::fake([TextResponseFake::make()->withText( $expected )]);

        $response = $this->actingAs( $this->user )->graphQL( "
            mutation {
                compose(prompt: \"Generate content\", lang: \"en\", context: \"This is a test context.\")
            }
        " )->assertJson( [
            'data' => [
                'compose' => $expected
            ]
        ] );
    }
}
