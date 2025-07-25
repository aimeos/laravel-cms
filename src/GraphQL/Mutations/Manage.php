<?php

namespace Aimeos\Cms\GraphQL\Mutations;

use Prism\Prism\Prism;
use Prism\Prism\Enums\ToolChoice;
use Prism\Prism\ValueObjects\Media\Audio;
use Prism\Prism\ValueObjects\Media\Image;
use Prism\Prism\ValueObjects\Media\Video;
use Prism\Prism\ValueObjects\Media\Document;
use Prism\Prism\ValueObjects\ProviderTool;
use Aimeos\Cms\GraphQL\Exception;
use Aimeos\Cms\Models\File;


final class Manage
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

        $system = view( 'cms::prompts.manage' )->render() . "\n"
            . view( 'cms::prompts.compose' )->render() . "\n";

        $files = [];
        $prism = Prism::text()->using( config( 'cms.ai.text', 'gemini' ), config( 'cms.ai.text-model', 'gemini-2.0-flash' ) )
            ->withSystemPrompt( $system . "\n" . ($args['context'] ?? '') )
            ->withTools( \Aimeos\Cms\Tools::get() )
            ->withToolChoice( ToolChoice::Any )
            ->withMaxSteps( 5 );

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

        $response = $prism->withPrompt( $args['prompt'], $files )->asText();

        return $response->text;
    }
}
