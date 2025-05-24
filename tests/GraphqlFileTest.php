<?php

namespace Tests;

use Illuminate\Http\UploadedFile;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Nuwave\Lighthouse\Testing\RefreshesSchemaCache;
use Database\Seeders\CmsSeeder;
use Aimeos\Cms\Models\File;


class GraphqlFileTest extends TestAbstract
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
            'cmseditor' => 0x7fffffff
        ]);
    }


    public function testFile()
    {
        $this->seed( CmsSeeder::class );

        $file = File::firstOrFail();

        $attr = collect($file->getAttributes())->except(['tenant_id'])->all();
        $expected = ['id' => (string) $file->id] + $attr + ['byelements' => [], 'bypages' => [], 'byversions' => [['published' => true]]];

        $this->expectsDatabaseQueryCount( 4 );
        $response = $this->actingAs( $this->user )->graphQL( "{
            file(id: \"{$file->id}\") {
                id
                tag
                mime
                name
                path
                previews
                editor
                created_at
                updated_at
                deleted_at
                byelements {
                    id
                }
                bypages {
                    id
                }
                byversions {
                    published
                }
            }
        }" )->assertJson( [
            'data' => [
                'file' => $expected,
            ]
        ] );
    }


    public function testFiles()
    {
        $this->seed( CmsSeeder::class );

        $file = File::where( 'tag', 'test' )->get()->first();

        $attr = collect($file->getAttributes())->except(['tenant_id'])->all();
        $expected = [['id' => (string) $file->id] + $attr];

        $this->expectsDatabaseQueryCount( 2 );
        $response = $this->actingAs( $this->user )->graphQL( '{
            files(filter: {
                id: ["' . $file->id . '"]
                tag: "test"
                mime: "image/"
                name: "Test"
                editor: "seeder"
                any: "test"
            }, sort: [{column: MIME, order: ASC}], first: 10, trashed: WITH) {
                data {
                    id
                    tag
                    mime
                    name
                    path
                    previews
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
                'files' => [
                    'data' => $expected,
                    'paginatorInfo' => [
                        'currentPage' => 1,
                        'lastPage' => 1,
                    ]
                ],
            ]
        ] );
    }


    public function testAddFile()
    {
        $this->seed( CmsSeeder::class );

        $this->expectsDatabaseQueryCount( 2 );
        $response = $this->actingAs( $this->user )->multipartGraphQL( [
            'query' => '
                mutation($file: Upload!, $preview: Upload) {
                    addFile(file: $file, input: {
                        name: "Test file name"
                        tag: "test tag"
                    }, preview: $preview) {
                        id
                        tag
                        mime
                        name
                        path
                        previews
                        editor
                        created_at
                        updated_at
                    }
                }
            ',
            'variables' => [
                'file' => null,
                'preview' => null,
            ],
        ], [
            '0' => ['variables.file'],
            '1' => ['variables.preview'],
        ], [
            '0' => UploadedFile::fake()->create('test.pdf', 500),
            '1' => UploadedFile::fake()->image('test-preview-1.jpg', 20),
        ] );

        $element = json_decode( $response->getContent() );
        $id = $element?->data?->addFile?->id;
        $file = File::find( $id );

        $response->assertJson( [
            'data' => [
                'addFile' => [
                    'id' => $file->id,
                    'mime' => 'application/pdf',
                    'tag' => 'test tag',
                    'name' => 'Test file name',
                    'path' => $file->path,
                    'previews' => json_encode( $file->previews ),
                    'editor' => 'Test',
                    'created_at' => (string) $file->created_at,
                    'updated_at' => (string) $file->updated_at,
                ],
            ]
        ] );
    }


    public function testSaveFile()
    {
        $this->seed( CmsSeeder::class );

        $file = File::firstOrFail();

        $this->expectsDatabaseQueryCount( 3 );
        $response = $this->actingAs( $this->user )->multipartGraphQL( [
            'query' => '
                mutation($preview: Upload) {
                    saveFile(id: "' . $file->id . '", input: {
                        name: "test file"
                        tag: "test"
                    }, preview: $preview) {
                        id
                        tag
                        name
                        previews
                        editor
                    }
                }
            ',
            'variables' => [
                'preview' => null,
            ],
        ], [
            '0' => ['variables.preview'],
        ], [
            '0' => UploadedFile::fake()->image('test-preview-1.jpg', 20),
        ] );

        $file = File::find( $file->id );

        $response->assertJson( [
            'data' => [
                'saveFile' => [
                    'id' => $file->id,
                    'name' => 'test file',
                    'tag' => 'test',
                    'editor' => 'Test',
                    'previews' => json_encode( $file->previews ),
                ],
            ]
        ] );
    }


    public function testDropFile()
    {
        $this->seed( CmsSeeder::class );

        $file = File::firstOrFail();

        $this->expectsDatabaseQueryCount( 3 );
        $response = $this->actingAs( $this->user )->graphQL( '
            mutation {
                dropFile(id: "' . $file->id . '") {
                    id
                    deleted_at
                }
            }
        ' );

        $file = File::withTrashed()->find( $file->id );

        $response->assertJson( [
            'data' => [
                'dropFile' => [
                    'id' => $file->id,
                    'deleted_at' => $file->deleted_at,
                ],
            ]
        ] );
    }


    public function testKeepFile()
    {
        $this->seed( CmsSeeder::class );

        $file = File::firstOrFail();
        $file->delete();

        $this->expectsDatabaseQueryCount( 3 );
        $response = $this->actingAs( $this->user )->graphQL( '
            mutation {
                keepFile(id: "' . $file->id . '") {
                    id
                    deleted_at
                }
            }
        ' );

        $file = File::find( $file->id );

        $response->assertJson( [
            'data' => [
                'keepFile' => [
                    'id' => $file->id,
                    'deleted_at' => null,
                ],
            ]
        ] );
    }


    public function testPurgeFile()
    {
        $this->seed( CmsSeeder::class );

        $file = File::firstOrFail();

        $this->expectsDatabaseQueryCount( 3 );
        $response = $this->actingAs( $this->user )->graphQL( '
            mutation {
                purgeFile(id: "' . $file->id . '") {
                    id
                }
            }
        ' );

        $this->assertNull( File::where('id', $file->id)->first() );
    }
}
