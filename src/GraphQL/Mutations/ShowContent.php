<?php

namespace Aimeos\Cms\GraphQL\Mutations;

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

        DB::transaction( function() use ( $args, $content ) {
            DB::table( 'cms_contents' )->where( 'page_id', $content->page_id )->update( ['status' => 0] );
            DB::table( 'cms_contents' )->where( 'id', $args['id'] )->update( ['status' => 1] );
        } );

        return $content->id;
    }
}
