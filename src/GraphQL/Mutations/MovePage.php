<?php

namespace Aimeos\Cms\GraphQL\Mutations;

use Illuminate\Support\Facades\Auth;
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
        $page = Page::findOrFail( $args['id'] );
        $page->editor = Auth::user()?->name ?? request()->ip();

        if( isset( $args['ref'] ) ) {
            $page->beforeNode( Page::findOrFail( $args['ref'] ) );
        }
        elseif( isset( $args['parent'] ) ) {
            $page->appendToNode( Page::findOrFail( $args['parent'] ) );
        }
        else {
            DB::connection( config( 'cms.db', 'sqlite' ) )->transaction( fn() => $page->saveAsRoot(), 3 );
            return $page;
        }

        DB::connection( config( 'cms.db', 'sqlite' ) )->transaction( fn() => $page->save(), 3 );

        return $page;
    }
}
