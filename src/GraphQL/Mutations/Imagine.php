<?php

namespace Aimeos\Cms\GraphQL\Mutations;

use Prism\Prism\Prism;
use Prism\Prism\ValueObjects\Media\Audio;
use Prism\Prism\ValueObjects\Media\Image;
use Prism\Prism\ValueObjects\Media\Video;
use Prism\Prism\ValueObjects\Media\Document;
use Aimeos\Cms\GraphQL\Exception;
use Aimeos\Cms\Models\File;


final class Imagine
{
    /**
     * @param  null  $rootValue
     * @param  array<string, mixed>  $args
     */
    public function __invoke( $rootValue, array $args ): array
    {
        if( empty( $args['prompt'] ) ) {
            throw new Exception( 'Prompt must not be empty' );
        }

        $files = [];
        $prompt = join( "\n\n", array_filter( [
            view( 'cms::prompts.imagine' )->render(),
            $args['context'] ?? '',
            $args['prompt']
        ] ) );

        $prism = Prism::image()->using( config( 'cms.ai.image', 'openai' ), config( 'cms.ai.image-model', 'dall-e-3' ) );

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

                $disk = config( 'cms.disk', 'public' );

                return match( explode( '/', $file->mime )[0] ) {
                    'image' => Image::fromStoragePath( $file->path, $disk ),
                    'audio' => Audio::fromStoragePath( $file->path, $disk ),
                    'video' => Video::fromStoragePath( $file->path, $disk ),
                    default => Document::fromStoragePath( $file->path, $disk ),
                };
            } )->values()->toArray();
        }

        $response = $prism->withPrompt( $prompt, $files )->generate();

        $prompt = collect( $response->images )
            ->map( fn( $image ) => $image->hasRevisedPrompt() ? $image->revisedPrompt : null )
            ->filter()
            ->first() ?? $prompt;

        $urls = collect( $response->images )
            ->map( fn( $image ) => $image->hasUrl() ? $image->url : null )
            ->filter()
            ->toArray();

        return array_merge( [$prompt], $urls );
    }
}
