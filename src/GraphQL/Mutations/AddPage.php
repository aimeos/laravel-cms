<?php

namespace Aimeos\Cms\GraphQL\Mutations;

use Illuminate\Support\Facades\Auth;
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
        $page = new Page( $args['input'] ?? [] );
        $page->editor = Auth::user()?->name ?? request()->ip();

        if( isset( $args['ref'] ) ) {
            $page->beforeNode( Page::findOrFail( $args['ref'] ) );
        }
        elseif( isset( $args['parent'] ) ) {
            $page->appendToNode( Page::findOrFail( $args['parent'] ) );
        }

        DB::connection( config( 'cms.db', 'sqlite' ) )->transaction( fn() => $page->save(), 3 );

        return $page;
    }
}
