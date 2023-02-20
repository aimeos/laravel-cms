<?php

namespace Aimeos\Cms\GraphQL\Mutations;

use Illuminate\Support\Facades\DB;
use Aimeos\Cms\Models\Page;


final class SavePage
{
    /**
     * @param  null  $rootValue
     * @param  array  $args
     */
    public function __invoke( $rootValue, array $args ) : Page
    {
        $node = Page::findOrFail( $args['id'] )->fill( $args['input'] ?? [] );

        DB::transaction( fn() => $node->save(), 3 );

        return $node;
    }
}
