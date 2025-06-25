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

        $prism = Prism::text()->using( config( 'cms.ai.text', 'openai' ), config( 'cms.ai.text-model', 'chatgpt-4o-latest' ) );

        if( !empty( $args['context'] ) ) {
            $prism->withSystemPrompt( $args['context'] );
        }

        $response = $prism->withSystemPrompt( view( 'cms::prompts.compose' ) )
            ->withPrompt( $args['prompt'] )
            ->asText();

        return $response->text;
    }
}
