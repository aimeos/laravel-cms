<?php

namespace Aimeos\Cms\GraphQL\Mutations;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Aimeos\Cms\Models\Content;


final class AddContent
{
    /**
     * @param  null  $rootValue
     * @param  array  $args
     */
    public function __invoke( $rootValue, array $args ) : Content
    {
        $content = new Content();

        DB::connection( config( 'cms.db', 'sqlite' ) )->transaction( function() use ( $content, $args ) {

            $editor = Auth::user()?->name ?? request()->ip();

            $content->fill( $args['input'] ?? [] );
            $content->tenant_id = \Aimeos\Cms\Tenancy::value();
            $content->editor = $editor;
            $content->save();

            if( isset( $args['input']['data'] ) )
            {
                $version = $content->versions()->create( [
                    'data' => $args['input']['data'],
                    'published' => false,
                    'editor' => $editor
                ] );

                $version->files()->attach( $args['input']['files'] ?? [] );
            }

        }, 3 );

        return Content::findOrFail( $content->id );
    }
}
