<?php

namespace Aimeos\Cms\GraphQL\Mutations;

use Prism\Prism\Prism;
use Prism\Prism\ValueObjects\Media\Audio;
use Prism\Prism\ValueObjects\Media\Image;
use Prism\Prism\ValueObjects\Media\Video;
use Prism\Prism\ValueObjects\Media\Document;
use Prism\Prism\ValueObjects\ProviderTool;
use Aimeos\Cms\GraphQL\Exception;
use Aimeos\Cms\Models\File;


final class Compose
{
    /**
     * @param  null  $rootValue
     * @param  array<string, mixed>  $args
     */
    public function __invoke( $rootValue, array $args ): string
    {
        if( empty( $args['prompt'] ) ) {
            throw new Exception( 'Prompt must not be empty' );
        }

        $files = [];
        $prism = Prism::text()->using( config( 'cms.ai.text', 'gemini' ), config( 'cms.ai.text-model', 'gemini-2.0-flash' ) )
            ->withSystemPrompt( view( 'cms::prompts.compose' )->render() . "\n" . ($args['context'] ?? '') )
            ->withTools( [new \Aimeos\Cms\Tools\Pages()] )
            ->withMaxSteps( 10 );

        $prism->whenProvider( 'gemini',
            fn( $request ) => $request->withProviderTools( [
                new ProviderTool( 'google_search' )
            ] )
        );

        if( !empty( $ids = $args['files'] ?? null ) )
        {
            $files = File::where( 'id', $ids )->get()->map( function( $file ) {

                if( str_starts_with( $file->path, 'http' ) )
                {
                    return match( explode( '/', $file->mime )[0] ) {
                        'image' => Image::fromUrl( $file->path ),
                        'audio' => Audio::fromUrl( $file->path ),
                        'video' => Video::fromUrl( $file->path ),
                        default => Document::fromUrl( $file->path ),
                    };
                }

                return match( explode( '/', $file->mime )[0] ) {
                    'image' => Image::fromStoragePath( $file->path, 'public' ),
                    'audio' => Audio::fromStoragePath( $file->path, 'public' ),
                    'video' => Video::fromStoragePath( $file->path, 'public' ),
                    default => Document::fromStoragePath( $file->path, 'public' ),
                };
            } )->values()->toArray();
        }

        $response = $prism->withPrompt( $args['prompt'], $files )->asText();

        return $response->text;
    }
}
