<?php

namespace Aimeos\Cms\GraphQL\Mutations;

use Prism\Prism\Prism;
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

        if( empty( $args['lang'] ) ) {
            throw new Exception( 'Language must not be empty' );
        }

        $prism = Prism::text()->using( config( 'cms.ai.text', 'openai' ), '' );

        if( !empty( $args['context'] ) ) {
            $prism->withSystemPrompt( $args['context'] );
        }

        $response = $prism->withSystemPrompt( view( 'cms::prompts.compose' ) )
            ->withSystemPrompt( 'Output must be in language with code "' . $args['lang'] . '"' )
            ->withPrompt( $args['prompt'] )
            ->asText();

        return $response->text;
    }
}
