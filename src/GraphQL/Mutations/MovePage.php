<?php

namespace Aimeos\Cms\GraphQL\Mutations;

use Illuminate\Support\Facades\DB;
use Aimeos\Cms\Models\Page;


final class MovePage
{
    /**
     * @param  null  $rootValue
     * @param  array  $args
     */
    public function __invoke( $rootValue, array $args ) : Page
    {
        $node = Page::findOrFail( $args['id'] );

        if( isset( $args['ref'] ) ) {
            $node->beforeNode( Page::findOrFail( $args['ref'] ) );
        } elseif( isset( $args['parent'] ) ) {
            $node->appendToNode( Page::findOrFail( $args['parent'] ) );
        }

        DB::connection( config( 'cms.db', 'sqlite' ) )->transaction( fn() => $node->save() ? $node->hasMoved() : null, 3 );

        return $node;
    }
}
