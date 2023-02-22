<?php

namespace Aimeos\Cms\GraphQL\Mutations;

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

        DB::transaction( function() use ( $args ) {
            DB::table( 'cms_contents' )->where( 'id', $content->id )->update( ['status' => 0] );
        } );

        Cache::forget( Page::key( $content->page->slug, $content->page->lang ) );

        return $content->id;
    }
}
