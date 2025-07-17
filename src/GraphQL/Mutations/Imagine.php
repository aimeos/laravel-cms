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

        $prompt = join( "\n\n", array_filter( [
            view( 'cms::prompts.imagine' )->render(),
            $args['context'] ?? '',
            $args['prompt']
        ] ) );

        $prism = Prism::image()->using( config( 'cms.ai.image', 'openai' ), config( 'cms.ai.image-model', 'dall-e-3' ) );

        $images = collect( $args['images'] ?? [] )->map( function( $image ) {
            if( str_starts_with( $image, 'http' ) ) {
                return Image::fromUrl( $image );
            } else {
                return Image::fromStoragePath( $image, 'public' );
            }
        } )->toArray();

        $response = $prism->withPrompt( $prompt, $images )->generate();

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
