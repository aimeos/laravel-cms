<?php

namespace Aimeos\Cms\GraphQL\Mutations;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Aimeos\Cms\Models\Content;
use Aimeos\Cms\Models\Ref;


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

                $version->files()->attach( $args['files'] ?? [] );
            }

            if( $pageId = $args['page_id'] ?? null )
            {
                $ref = new Ref();
                $ref->page_id = $pageId;
                $ref->content_id = $content->id;
                $ref->position = $args['position'] ?? 0;
                $ref->tenant_id = \Aimeos\Cms\Tenancy::value();
                $ref->editor = $editor;
                $ref->save();
            }

        }, 3 );

        return Content::findOrFail( $content->id );
    }
}
