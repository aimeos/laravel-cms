<?php

namespace Aimeos\Cms\GraphQL\Mutations;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

            if( isset( $args['input']['data'] ) )
            {
                $content->versions()->create( [
                    'data' => $args['input']['data'],
                    'published' => false,
                    'editor' => $editor
                ] );
            }

            $content->files()->syncWithPivotValues( $args['files'] ?? [], ['tenant_id' => \Aimeos\Cms\Tenancy::value()] );

        }, 3 );

        return $content;
    }
}
