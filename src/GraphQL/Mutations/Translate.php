<?php

namespace Aimeos\Cms\GraphQL\Mutations;

use Illuminate\Support\Facades\Http;
use Aimeos\Cms\GraphQL\Exception;


final class Translate
{
    /**
     * @param  null  $rootValue
     * @param  array<string, mixed>  $args
     */
    public function __invoke( $rootValue, array $args ): array
    {
        if( empty( $args['texts'] ) ) {
            throw new Exception( 'Input texts must not be empty' );
        }

        if( empty( $args['to'] ) ) {
            throw new Exception( 'Target language must not be empty' );
        }

        if( empty( $apiKey = config( 'services.deepl.key' ) ) ) {
            throw new Exception( 'DeepL API key must be configured' );
        }

        $url = rtrim( config( 'services.deepl.url', 'https://api-free.deepl.com/v2/translate' ), '/' );
        $payload = [
            'ignore_tags' => ['x'],
            'tag_handling' => 'xml',
            'preserve_formatting' => true,
            'model_type' => 'prefer_quality_optimized',
            'target_lang' => strtoupper( $args['to'] ),
            'source_lang' => strtoupper( $args['from'] ?? '' ),
            'context' => $args['context'] ?? '',
            'text' => $args['texts'],
        ];

        $response = Http::withHeaders([
            'Authorization' => 'DeepL-Auth-Key ' . $apiKey,
            'Content-Type' => 'application/json'
        ])->post( $url, $payload )->throw();

        return collect( $response->json( 'translations' ) )->pluck( 'text' )->toArray();
    }
}
