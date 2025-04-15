<?php

namespace Aimeos\Cms\GraphQL\Mutations;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Aimeos\Cms\Models\Version;
use Aimeos\Cms\Models\Content;


final class SaveContent
{
    /**
     * @param  null  $rootValue
     * @param  array  $args
     */
    public function __invoke( $rootValue, array $args ) : Content
    {
        $content = Content::findOrFail( $args['id'] );

        DB::connection( config( 'cms.db', 'sqlite' ) )->transaction( function() use ( $content, $args ) {

            $editor = Auth::user()?->name ?? request()->ip();

            $content->fill( $args['input'] ?? [] );
            $content->editor = $editor;
            $content->save();

            if( isset( $args['input']['data'] ) && $args['input']['data'] !== $content->latest?->data )
            {
                $version = $content->versions()->create( [
                    'data' => $args['input']['data'],
                    'published' => false,
                    'editor' => $editor
                ] );

                $version->files()->sync( $args['input']['files'] ?? [] );

                $ids = Version::select( 'id' )
                    ->where( 'versionable_id', $content->id )
                    ->where( 'versionable_type', Content::class )
                    ->where( 'published', false )
                    ->orderBy( 'id' )
                    ->skip( 10 )
                    ->take( 10 )
                    ->pluck( 'id' );

                Version::whereIn( 'id', $ids )->forceDelete();
            }

        }, 3 );

        return $content;
    }
}
