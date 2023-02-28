<?php

namespace Aimeos\Cms\GraphQL\Mutations;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Aimeos\Cms\Models\Content;


final class ShowContent
{
    /**
     * @param  null  $rootValue
     * @param  array  $args
     */
    public function __invoke( $rootValue, array $args ) : string
    {
        $content = Content::findOrFail( $args['id'] );
        $editor = Auth::user()?->name ?? request()->ip();

        DB::connection( config( 'cms.db', 'sqlite' ) )->transaction( function() use ( $args, $content, $editor ) {
            DB::connection( config( 'cms.db', 'sqlite' ) )
                ->table( 'cms_contents' )
                ->where( 'status', 1 )
                ->where( 'page_id', $content->page_id )
                ->update( ['status' => 0, 'updated_at' => date( 'Y-m-d H:i:s' ), 'editor' => $editor] );

            DB::connection( config( 'cms.db', 'sqlite' ) )
                ->table( 'cms_contents' )
                ->where( 'id', $args['id'] )
                ->update( ['status' => 1, 'updated_at' => date( 'Y-m-d H:i:s' ), 'editor' => $editor] );
        } );

        Cache::forget( Page::key( $content->page->slug, $content->page->lang ) );

        return $content->id;
    }
}
