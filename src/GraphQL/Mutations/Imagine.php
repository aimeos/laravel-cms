<?php

namespace Aimeos\Cms\GraphQL\Mutations;

use Prism\Prism\Prism;
use Prism\Prism\ValueObjects\Messages\UserMessage;
use Prism\Prism\ValueObjects\Messages\Support\Image;
use Aimeos\Cms\GraphQL\Exception;


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

        $prism = Prism::image()->using( config( 'cms.ai.image', 'openai' ), config( 'cms.ai.image-model', 'dall-e-3' ) );

        if( !empty( $args['context'] ) ) {
            $prism->withSystemPrompt( $args['context'] );
        }

        if( !empty( $args['images'] ) )
        {
            $images = collect( $args['images'] )->map( function( $image ) {
                if( str_starts_with( $image, 'http' ) ) {
                    return Image::fromUrl( $image );
                } else {
                    return Image::fromStoragePath( $image, 'public' );
                }
            } )->toArray();

            $prism->withMessages( [new UserMessage( $args['prompt'], $images )] );
        }
        else
        {
            $prism->withPrompt( $args['prompt'] );
        }

        $response = $prism->withSystemPrompt( view( 'cms::prompts.imagine' ) )->generate();

        return collect( $response->images )->map( fn( Image $image ) => $image->url )->toArray();
    }
}
