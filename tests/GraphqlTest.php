<?php

namespace Tests;

use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Nuwave\Lighthouse\Testing\RefreshesSchemaCache;
use Prism\Prism\Testing\ImageResponseFake;
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
                compose(prompt: \"Generate content\", context: \"This is a test context.\")
            }
        " )->assertJson( [
            'data' => [
                'compose' => $expected
            ]
        ] );
    }


    public function testImagine()
    {
        Prism::fake([ImageResponseFake::make()]);

        $response = $this->actingAs( $this->user )->graphQL( "
            mutation {
                imagine(prompt: \"Generate content\", context: \"This is a test context.\", images: [\"https://example.com/image1.jpg\", \"https://example.com/image2.jpg\"])
            }
        " )->assertJson( [
            'data' => [
                'imagine' => [
                    'You are a graphic designer specializing in clean, web-optimized images for modern websites.
All images should have a clear focal point, avoid clutter, and use balanced composition suitable for responsive web layouts.
Use lighting and color schemes that align with a professional, accessible, and visually appealing design.
Do not include text or watermarks. Ensure backgrounds are clean or transparent unless otherwise specified.
If example images are provided, the results should match the style and aspect ratio of those examples.

This is a test context.

Generate content',
                    'https://example.com/fake-image.png'
                ]
            ]
        ] );
    }
}
