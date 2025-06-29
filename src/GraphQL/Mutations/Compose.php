<?php

namespace Aimeos\Cms\GraphQL\Mutations;

use Prism\Prism\Prism;
use Prism\Prism\ValueObjects\Messages\UserMessage;
use Prism\Prism\ValueObjects\Messages\Support\Image;
use Aimeos\Cms\GraphQL\Exception;


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

        $prism = Prism::text()->using( config( 'cms.ai.text', 'openai' ), config( 'cms.ai.text-model', 'chatgpt-4o-latest' ) );

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

        $response = $prism->withSystemPrompt( view( 'cms::prompts.compose' ) )->asText();

        return $response->text;
    }
}
