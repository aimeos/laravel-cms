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
        DB::transaction( function() use ( $args ) {
            DB::table( 'cms_contents' )->where( 'id', $args['id'] )->update( ['status' => 0] );
        } );

        return $args['id'];
    }
}
