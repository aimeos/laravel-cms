<?php

namespace Aimeos\Cms\GraphQL\Mutations;

use Illuminate\Support\Facades\DB;
use Aimeos\Cms\Models\Page;


final class AddPage
{
    /**
     * @param  null  $rootValue
     * @param  array  $args
     */
    public function __invoke( $rootValue, array $args ) : Page
    {
        $node = new Page( $args['input'] ?? [] );

        if( isset( $args['ref'] ) ) {
            $node->beforeNode( Page::findOrFail( $args['ref'] ) );
        }
        elseif( isset( $args['parent'] ) ) {
            $node->appendToNode( Page::findOrFail( $args['parent'] ) );
        }

        DB::connection( config( 'cms.db', 'sqlite' ) )->transaction( fn() => $node->save(), 3 );

        return $node;
    }
}
