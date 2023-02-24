<?php

namespace Aimeos\Cms\GraphQL\Mutations;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Aimeos\Cms\Models\Content;


final class HideContent
{
    /**
     * @param  null  $rootValue
     * @param  array  $args
     */
    public function __invoke( $rootValue, array $args ) : string
    {
        $content = Content::findOrFail( $args['id'] );
        $content->editor = Auth::user()?->email ?? request()->ip();

        DB::connection( config( 'cms.db', 'sqlite' ) )->transaction( function() use ( $args ) {
            DB::connection( config( 'cms.db', 'sqlite' ) )
                ->table( 'cms_contents' )
                ->where( 'id', $content->id )
                ->update( ['status' => 0] );
        } );

        Cache::forget( Page::key( $content->page->slug, $content->page->lang ) );

        return $content->id;
    }
}
